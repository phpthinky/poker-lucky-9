<?php

namespace App\Services;

use App\Events\BetPlaced;
use App\Events\RoundStarted;
use App\Jobs\ProcessRoundTimer;
use App\Models\ActivityFeed;
use App\Models\GameRound;
use App\Models\Player;
use App\Models\RoundBet;
use App\Models\RoundStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * GameEngineService
 *
 * Handles player-facing actions:
 *   - createWaitingRound()  → Called on boot and after each finished round
 *   - placeBet()            → Called from GameController on chip click
 *   - startBetting()        → Called automatically on first bet of a round
 *
 * The timer, dealing, payouts, and round reset are all handled
 * inside ProcessRoundTimer job running on the 'game' queue.
 */
class GameEngineService
{
    public function __construct(
        private readonly RedisStateService $redis,
        private readonly DeckService       $deck,
        private readonly PayoutService     $payouts,
    ) {}

    // ─── ROUND CREATION ─────────────────────────────────────────────────────

    /**
     * Create a fresh 'waiting' round and push it to Redis.
     * Call this on first server boot and after each round finishes.
     */
    public function createWaitingRound(): GameRound
    {
        $round = GameRound::create(['round_status' => RoundStatus::Waiting]);

        $this->redis->setRound(array_merge($round->toArray(), ['timer_remaining' => 0]));

        Log::info("[Engine] Waiting round {$round->id} created");

        return $round;
    }

    // ─── BET ────────────────────────────────────────────────────────────────

    /**
     * Place (or update) a player's bet.
     * If the round is still 'waiting', automatically starts it.
     *
     * Called by: GameController::placeBet()
     */
    public function placeBet(GameRound $round, Player $player, array $bets, int $totalBet): RoundBet
    {
        $isFirstBet = $round->round_status === RoundStatus::Waiting;

        // How much has this player already committed to this round?
        // We only deduct the ADDITIONAL chips placed in this click, not the
        // cumulative total — otherwise each chip click deducts way too much.
        $existingBet = RoundBet::where('round_id', $round->id)
            ->where('player_id', $player->id)
            ->value('total_bet') ?? 0;

        $betDelta = max(0, $totalBet - $existingBet);

        $bet = DB::transaction(function () use ($round, $player, $bets, $totalBet, $betDelta, $isFirstBet) {

            // Start betting on first chip click
            if ($isFirstBet) {
                $this->startBetting($round);
            }

            // Upsert — handles repeated chip clicks updating the same bet
            $bet = RoundBet::updateOrCreate(
                [
                    'round_id'  => $round->id,
                    'player_id' => $player->id,
                ],
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

            // Deduct only the chips added in THIS click, not the whole cumulative bet.
            if ($betDelta > 0) {
                $player->decrement('balance', $betDelta);
            }

            return $bet;
        });

        // Track in Redis (for active player count shown in UI)
        $this->redis->addActivePlayer($player->id, $player->player_name);

        // Log activity for the feed (every single click, shows cumulative)
        ActivityFeed::create([
            'round_id'      => $round->id,
            'player_id'     => $player->id,
            'player_name'   => $player->player_name,
            'activity_type' => 'placed_bet',
            'amount'        => $totalBet,
            'message'       => "{$player->player_name} bet {$totalBet} WPUFF",
            'created_at'    => now(),
        ]);

        // Broadcast to all other players (activity feed update)
        $activePlayers = count($this->redis->getActivePlayers());
        broadcast(new BetPlaced(
            roundId:       $round->id,
            playerName:    $player->player_name,
            totalBet:      $totalBet,
            activePlayers: $activePlayers,
        ))->toOthers(); // Exclude the player who placed — they already know

        Log::info("[Engine] Bet placed: player={$player->id} total={$totalBet} round={$round->id}");

        return $bet;
    }

    // ─── START BETTING ───────────────────────────────────────────────────────

    /**
     * Transition round: waiting → betting
     * Starts the 20-second countdown by dispatching ProcessRoundTimer to queue.
     *
     * NOTE: Called inside a DB transaction from placeBet(),
     *       so the job is dispatched AFTER the transaction commits.
     */
    private function startBetting(GameRound $round): void
    {
        $duration = config('game.betting_duration', 20);

        $round->update([
            'round_status' => RoundStatus::Betting,
            'started_at'   => now(),
        ]);

        // Update cached state
        $this->redis->setTimerEndsAt(now()->addSeconds($duration)->timestamp);
        $this->redis->updateRoundField('round_status', RoundStatus::Betting->value);
        $this->redis->updateRoundField('started_at', now()->toIso8601String());

        // Dispatch the timer job — runs on 'game' queue (database connection).
        // On shared hosting (Hostinger) there is no persistent worker, so this
        // job will stay queued until a cron-based worker picks it up.
        // Client-side countdown in game.js means the UI timer still shows
        // immediately without waiting for this job.
        ProcessRoundTimer::dispatch($round->id)->onConnection('database')->afterCommit();
        Log::info("[Engine] Betting started for round {$round->id} ({$duration}s) — job queued on database");
    }
}
