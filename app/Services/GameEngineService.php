<?php

namespace App\Services;

use App\Events\BetPlaced;
use App\Events\CardsDealt;
use App\Events\PlayerResult;
use App\Events\RoundFinished;
use App\Events\RoundStarted;
use App\Models\ActivityFeed;
use App\Models\GameRound;
use App\Models\Player;
use App\Models\RoundBet;
use App\Models\RoundStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * GameEngineService — time-based state machine, no queue worker needed.
 *
 * State flow:
 *   WAITING  → (first bet)   → BETTING   (round_ends_at set once)
 *   BETTING  → (any request after round_ends_at) → DEALING (atomic DB update)
 *   DEALING  → (inline after deal)  → FINISHED → new WAITING round
 *
 * Dealing is triggered by the first client request that arrives after
 * round_ends_at passes. The transition is guarded by an atomic UPDATE …
 * WHERE round_status='betting' AND round_ends_at <= now(), so only one
 * request can win the race regardless of concurrent clients.
 *
 * No cron, no queue worker, no background process required.
 */
class GameEngineService
{
    public function __construct(
        private readonly RedisStateService $redis,
        private readonly DeckService       $deck,
        private readonly PayoutService     $payouts,
    ) {}

    // ─── ROUND CREATION ─────────────────────────────────────────────────────

    public function createWaitingRound(): GameRound
    {
        $round = GameRound::create(['round_status' => RoundStatus::Waiting]);

        Log::info("[Engine] Waiting round {$round->id} created");

        return $round;
    }

    // ─── BET ────────────────────────────────────────────────────────────────

    /**
     * Place (or update) a player's bet.
     * If the round is still 'waiting', automatically transitions to betting.
     *
     * Called by: GameController::placeBet()
     */
    public function placeBet(GameRound $round, Player $player, array $bets, int $totalBet): RoundBet
    {
        $isFirstBet = $round->round_status === RoundStatus::Waiting;

        // Only deduct chips ADDED in this click — not the whole cumulative total.
        $existingBet = RoundBet::where('round_id', $round->id)
            ->where('player_id', $player->id)
            ->value('total_bet') ?? 0;

        $betDelta = max(0, $totalBet - $existingBet);

        $bet = DB::transaction(function () use ($round, $player, $bets, $totalBet, $betDelta, $isFirstBet) {

            if ($isFirstBet) {
                $this->startBetting($round);
            }

            $bet = RoundBet::updateOrCreate(
                ['round_id' => $round->id, 'player_id' => $player->id],
                [
                    'player_name'     => $player->player_name,
                    'bet_player'      => $bets['player']     ?? 0,
                    'bet_banker'      => $bets['banker']     ?? 0,
                    'bet_tie'         => $bets['tie']        ?? 0,
                    'bet_player_pair' => $bets['playerPair'] ?? 0,
                    'bet_banker_pair' => $bets['bankerPair'] ?? 0,
                    'bet_random_pair' => $bets['randomPair'] ?? 0,
                    'total_bet'       => $totalBet,
                ]
            );

            if ($betDelta > 0) {
                $player->decrement('balance', $betDelta);
            }

            return $bet;
        });

        $this->redis->addActivePlayer($player->id, $player->player_name);

        ActivityFeed::create([
            'round_id'      => $round->id,
            'player_id'     => $player->id,
            'player_name'   => $player->player_name,
            'activity_type' => 'placed_bet',
            'amount'        => $totalBet,
            'message'       => "{$player->player_name} bet {$totalBet} WPUFF",
            'created_at'    => now(),
        ]);

        $activePlayers = count($this->redis->getActivePlayers());
        broadcast(new BetPlaced(
            roundId:       $round->id,
            playerName:    $player->player_name,
            totalBet:      $totalBet,
            activePlayers: $activePlayers,
        ))->toOthers();

        Log::info("[Engine] Bet placed: player={$player->id} total={$totalBet} round={$round->id}");

        return $bet;
    }

    // ─── ATOMIC DEALING TRANSITION ───────────────────────────────────────────

    /**
     * Attempt to transition a betting round → dealing.
     *
     * Safe to call from any HTTP request; only ONE caller wins the DB-level race.
     * Returns the freshened round if this call won the race (and dealing is done),
     * or null if time hasn't come yet or another request already dealt.
     *
     * Called by: GameController::state() (and placeBet() on expired rounds)
     */
    public function attemptDeal(GameRound $round): ?GameRound
    {
        // Atomic: exactly one request can win this UPDATE.
        $won = DB::table('game_rounds')
            ->where('id', $round->id)
            ->where('round_status', RoundStatus::Betting->value)
            ->where('round_ends_at', '<=', now())
            ->update(['round_status' => RoundStatus::Dealing->value]);

        if ($won === 0) {
            // Time not up yet, or another request already transitioned.
            return null;
        }

        $round->refresh();

        Log::info("[Engine] Won dealing race for round {$round->id}");

        // No bets placed → reset to waiting instead of dealing
        if ($round->bets()->count() === 0) {
            Log::info("[Engine] No bets in round {$round->id} — resetting to waiting");
            return $this->resetToWaiting($round);
        }

        // Deal cards
        [$playerCards, $bankerCards] = $this->deck->deal();

        $playerTotal  = $this->deck->calculateTotal($playerCards);
        $bankerTotal  = $this->deck->calculateTotal($bankerCards);
        $result       = $this->determineResult($playerTotal, $bankerTotal);
        $isPlayerPair = $this->deck->isPair($playerCards);
        $isBankerPair = $this->deck->isPair($bankerCards);
        $isRandomPair = $this->deck->isRandomPair($playerCards, $bankerCards);

        $round->update([
            'player_cards'   => $playerCards,
            'banker_cards'   => $bankerCards,
            'player_total'   => $playerTotal,
            'banker_total'   => $bankerTotal,
            'result'         => $result,
            'is_player_pair' => $isPlayerPair,
            'is_banker_pair' => $isBankerPair,
            'is_random_pair' => $isRandomPair,
        ]);

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

        Log::info("[Engine] Cards dealt — result: {$result}");

        $this->finishRound($round->fresh());

        return $round->fresh();
    }

    // ─── START BETTING ───────────────────────────────────────────────────────

    /**
     * Transition waiting → betting.
     * Sets round_ends_at once; broadcasts RoundStarted so all clients start their countdown.
     * No queue job dispatched — the frontend polls state() to trigger dealing.
     *
     * Called inside a DB transaction from placeBet().
     */
    private function startBetting(GameRound $round): void
    {
        $duration = config('game.betting_duration', 20);
        $endsAt   = now()->addSeconds($duration);

        $round->update([
            'round_status'  => RoundStatus::Betting,
            'started_at'    => now(),
            'round_ends_at' => $endsAt,
        ]);

        // Broadcast immediately so all connected clients start their countdown.
        // ShouldBroadcastNow fires synchronously via Pusher HTTP API.
        broadcast(new RoundStarted($round->id, $endsAt->toIso8601String(), 'betting'));

        Log::info("[Engine] Betting started for round {$round->id}, ends at {$endsAt}");
    }

    // ─── PAYOUTS + FINISH ────────────────────────────────────────────────────

    private function finishRound(GameRound $round): void
    {
        $bets = $round->bets()->with('player')->get();

        foreach ($bets as $bet) {
            $winnings  = $this->payouts->calculate($bet, $round);
            $netChange = $winnings - $bet->total_bet;

            DB::transaction(function () use ($bet, $winnings, $netChange) {
                $bet->update(['total_won' => $winnings]);
                if ($winnings > 0) {
                    $bet->player->increment('balance', $winnings);
                }
                $bet->player->increment('total_games_played');
                $bet->player->increment('total_winnings', max(0, $netChange));
            });

            broadcast(new PlayerResult(
                playerId:   $bet->player_id,
                result:     $round->result,
                totalBet:   $bet->total_bet,
                totalWon:   $winnings,
                profit:     $netChange,
                newBalance: $bet->player->fresh()->balance,
            ));

            Log::info("[Engine] Player {$bet->player_id}: bet={$bet->total_bet} won={$winnings} net={$netChange}");
        }

        $resultDuration = config('game.result_duration', 5);

        $round->update(['round_status' => RoundStatus::Finished, 'finished_at' => now()]);

        broadcast(new RoundFinished(
            roundId:     $round->id,
            result:      $round->result,
            playerTotal: $round->player_total,
            bankerTotal: $round->banker_total,
            nextRoundIn: $resultDuration,
        ));

        Log::info("[Engine] Round {$round->id} finished — creating new waiting round");

        $this->startNewRound();
    }

    // ─── NEW ROUND ───────────────────────────────────────────────────────────

    private function startNewRound(): void
    {
        $newRound = GameRound::create(['round_status' => RoundStatus::Waiting]);

        $this->redis->flushGameState();

        broadcast(new RoundStarted($newRound->id, null, 'waiting'));

        Log::info("[Engine] New waiting round {$newRound->id} created");
    }

    // ─── RESET ───────────────────────────────────────────────────────────────

    private function resetToWaiting(GameRound $round): null
    {
        $round->update([
            'round_status'  => RoundStatus::Waiting,
            'started_at'    => null,
            'round_ends_at' => null,
        ]);

        broadcast(new RoundStarted($round->id, null, 'waiting'));

        Log::info("[Engine] Round {$round->id} reset to waiting (no bets)");

        return null;
    }

    // ─── HELPERS ─────────────────────────────────────────────────────────────

    private function determineResult(int $playerTotal, int $bankerTotal): string
    {
        return match (true) {
            $playerTotal > $bankerTotal => 'PLAYER_WINS',
            $bankerTotal > $playerTotal => 'BANKER_WINS',
            default                     => 'TIE',
        };
    }
}
