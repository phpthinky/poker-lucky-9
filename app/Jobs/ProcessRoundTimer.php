<?php

namespace App\Jobs;

use App\Events\CardsDealt;
use App\Events\PlayerResult;
use App\Events\RoundFinished;
use App\Events\RoundStarted;
use App\Events\TimerTick;
use App\Models\GameRound;
use App\Models\RoundStatus;
use App\Services\DeckService;
use App\Services\GameEngineService;
use App\Services\PayoutService;
use App\Services\RedisStateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ProcessRoundTimer
 *
 * This job is the heartbeat of Lucky Puffin.
 * It is dispatched to the 'game' Redis queue when betting starts,
 * and runs the full round lifecycle from countdown to new round creation.
 *
 * Timeline:
 *   t=0:    Job starts, broadcasts RoundStarted
 *   t=1..19: Each second broadcasts TimerTick
 *   t=20:   Deals cards → broadcasts CardsDealt
 *           Calculates payouts → broadcasts PlayerResult (private, per player)
 *           Broadcasts RoundFinished
 *   t=25:   Creates new waiting round → broadcasts RoundStarted(status=waiting)
 *
 * Run on queue: 'game'
 *   php artisan queue:work redis --queue=game,default
 */
class ProcessRoundTimer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Prevent duplicate jobs from piling up if worker restarts.
     * Only one timer per round allowed.
     */
    public string $uniqueId;

    public int $tries = 1;             // Don't retry — avoid double dealing
    public int $timeout = 120;         // 20s betting + 5s dealing + 5s result + buffer

    public function __construct(
        private readonly int $roundId,
    ) {
        $this->onQueue('game');
        $this->uniqueId = "round-timer-{$roundId}";
    }

    public function handle(
        GameEngineService $engine,
        RedisStateService $redis,
        DeckService       $deck,
        PayoutService     $payouts,
    ): void {
        Log::info("[Timer] Starting round {$this->roundId}");

        $round = GameRound::find($this->roundId);

        if (! $round) {
            Log::error("[Timer] Round {$this->roundId} not found — aborting");
            return;
        }

        // ── Phase 1: Countdown ──────────────────────────────────────────────

        $duration = config('game.betting_duration', 20);

        broadcast(new RoundStarted($round->id, $duration));

        for ($second = $duration; $second >= 0; $second--) {

            // Re-check round still exists and hasn't been manually reset
            $round = $round->fresh();
            if (! $round || $round->round_status === RoundStatus::Finished) {
                Log::warning("[Timer] Round {$this->roundId} ended early — stopping timer");
                return;
            }

            broadcast(new TimerTick($round->id, $second));

            if ($second > 0) {
                sleep(1);
            }
        }

        // ── Phase 2: Deal Cards ─────────────────────────────────────────────

        Log::info("[Timer] Timer expired — dealing cards for round {$this->roundId}");

        $round = $round->fresh();

        // Guard: already dealt (e.g. admin forced a deal)
        if ($round->hasCards()) {
            Log::info("[Timer] Cards already dealt — skipping deal");
            $this->finishRound($round, $redis, $payouts);
            return;
        }

        // Check if any bets placed — skip dealing if nobody bet
        $betCount = $round->bets()->count();
        if ($betCount === 0) {
            Log::info("[Timer] No bets placed — resetting to waiting");
            $this->resetToWaiting($round, $redis);
            return;
        }

        // Deal!
        [$playerCards, $bankerCards] = $deck->deal();

        $playerTotal  = $deck->calculateTotal($playerCards);
        $bankerTotal  = $deck->calculateTotal($bankerCards);
        $result       = $this->determineResult($playerTotal, $bankerTotal);
        $isPlayerPair = $deck->isPair($playerCards);
        $isBankerPair = $deck->isPair($bankerCards);
        $isRandomPair = $deck->isRandomPair($playerCards, $bankerCards);

        $dealingDuration = config('game.dealing_duration', 5);

        $round->update([
            'round_status'    => RoundStatus::Dealing,
            'player_cards'    => $playerCards,
            'banker_cards'    => $bankerCards,
            'player_total'    => $playerTotal,
            'banker_total'    => $bankerTotal,
            'result'          => $result,
            'is_player_pair'  => $isPlayerPair,
            'is_banker_pair'  => $isBankerPair,
            'is_random_pair'  => $isRandomPair,
            'dealing_ends_at' => now()->addSeconds($dealingDuration),
        ]);

        $redis->updateRoundField('round_status', RoundStatus::Dealing->value);
        $redis->updateRoundField('player_cards', $playerCards);
        $redis->updateRoundField('banker_cards', $bankerCards);
        $redis->updateRoundField('result', $result);

        // Broadcast cards to ALL players at the same instant
        broadcast(new CardsDealt(
            roundId:      $round->id,
            playerCards:  $playerCards,
            bankerCards:  $bankerCards,
            playerTotal:  $playerTotal,
            bankerTotal:  $bankerTotal,
            result:       $result,
            isPlayerPair: $isPlayerPair,
            isBankerPair: $isBankerPair,
            isRandomPair: $isRandomPair,
        ));

        Log::info("[Timer] Cards dealt — result: {$result}");

        // Wait for card animation to complete on clients
        sleep($dealingDuration);

        // ── Phase 3: Calculate Payouts ──────────────────────────────────────

        $this->finishRound($round->fresh(), $redis, $payouts);
    }

    // ─── PAYOUT + FINISH ────────────────────────────────────────────────────

    private function finishRound(GameRound $round, RedisStateService $redis, PayoutService $payouts): void
    {
        Log::info("[Timer] Calculating payouts for round {$round->id}");

        $bets = $round->bets()->with('player')->get();

        foreach ($bets as $bet) {
            $winnings  = $payouts->calculate($bet, $round);
            $netChange = $winnings - $bet->total_bet;

            DB::transaction(function () use ($bet, $winnings, $netChange) {
                $bet->update(['total_won' => $winnings]);

                // Balance was decremented at bet time — add back winnings
                if ($winnings > 0) {
                    $bet->player->increment('balance', $winnings);
                }

                $bet->player->increment('total_games_played');
                $bet->player->increment('total_winnings', max(0, $netChange));
            });

            // Private result per player
            broadcast(new PlayerResult(
                playerId:   $bet->player_id,
                result:     $round->result,
                totalBet:   $bet->total_bet,
                totalWon:   $winnings,
                profit:     $netChange,
                newBalance: $bet->player->fresh()->balance,
            ));

            Log::info("[Timer] Player {$bet->player_id} ({$bet->player_name}): "
                . "bet={$bet->total_bet}, won={$winnings}, net={$netChange}");
        }

        // Mark round finished
        $round->update([
            'round_status' => RoundStatus::Finished,
            'finished_at'  => now(),
        ]);

        $redis->updateRoundField('round_status', RoundStatus::Finished->value);

        $resultDuration = config('game.result_duration', 5);

        broadcast(new RoundFinished(
            roundId:     $round->id,
            result:      $round->result,
            playerTotal: $round->player_total,
            bankerTotal: $round->banker_total,
            nextRoundIn: $resultDuration,
        ));

        Log::info("[Timer] Round {$round->id} finished");

        // Wait for result screen to show on clients
        sleep($resultDuration);

        // ── Phase 4: Create New Waiting Round ───────────────────────────────

        $this->startNewRound($redis);
    }

    // ─── NEW ROUND ──────────────────────────────────────────────────────────

    private function startNewRound(RedisStateService $redis): void
    {
        Log::info("[Timer] Creating new waiting round");

        $newRound = GameRound::create(['round_status' => RoundStatus::Waiting]);

        $roundData = array_merge($newRound->toArray(), ['timer_remaining' => 0]);
        $redis->setRound($roundData);
        $redis->clearActivePlayers();
        $redis->clearTimer();

        // Tell all clients a new round is waiting
        broadcast(new RoundStarted(
            roundId:      $newRound->id,
            timerSeconds: 0,
            status:       'waiting',
        ));

        Log::info("[Timer] New round {$newRound->id} created (waiting)");
    }

    // ─── RESET ──────────────────────────────────────────────────────────────

    private function resetToWaiting(GameRound $round, RedisStateService $redis): void
    {
        $round->update([
            'round_status' => RoundStatus::Waiting,
            'started_at'   => null,
        ]);

        $redis->updateRoundField('round_status', RoundStatus::Waiting->value);
        $redis->clearTimer();
        $redis->clearActivePlayers();

        broadcast(new RoundStarted(
            roundId:      $round->id,
            timerSeconds: 0,
            status:       'waiting',
        ));

        Log::info("[Timer] Round {$round->id} reset to waiting (no bets)");
    }

    // ─── HELPERS ────────────────────────────────────────────────────────────

    private function determineResult(int $playerTotal, int $bankerTotal): string
    {
        return match (true) {
            $playerTotal > $bankerTotal => 'PLAYER_WINS',
            $bankerTotal > $playerTotal => 'BANKER_WINS',
            default                     => 'TIE',
        };
    }

    /**
     * If this job fails (server crash etc.), log it clearly.
     * Do NOT retry — a partial deal or double payout is worse than a skipped round.
     */
    public function failed(\Throwable $e): void
    {
        Log::error("[Timer] FAILED for round {$this->roundId}: " . $e->getMessage());
        Log::error($e->getTraceAsString());

        // Attempt to reset round to waiting so game can continue
        try {
            $round = GameRound::find($this->roundId);
            if ($round && $round->round_status !== RoundStatus::Finished) {
                $round->update(['round_status' => RoundStatus::Waiting, 'started_at' => null]);
                Log::info("[Timer] Emergency reset: round {$this->roundId} → waiting");
            }
        } catch (\Throwable $resetError) {
            Log::error("[Timer] Could not reset round: " . $resetError->getMessage());
        }
    }
}
