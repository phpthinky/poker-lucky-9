<?php

namespace App\Services;

use App\Models\ActivityFeed;
use App\Models\GameRound;
use App\Models\Player;
use App\Models\RoundBet;
use App\Models\RoundStatus;
use Illuminate\Support\Facades\DB;

class GameEngineService
{
    public function __construct(
        private readonly RedisStateService $redis,
        private readonly DeckService       $deck,
        private readonly PayoutService     $payouts,
    ) {}

    // ─── ROUND LIFECYCLE ────────────────────────────────────────

    /**
     * Create a new waiting round and cache in Redis.
     */
    public function createWaitingRound(): GameRound
    {
        $round = GameRound::create(['round_status' => RoundStatus::Waiting]);
        $this->redis->setRound(array_merge($round->toArray(), ['timer_remaining' => 0]));
        return $round;
    }

    /**
     * Transition waiting → betting when first bet arrives.
     */
    public function startBetting(GameRound $round): void
    {
        $round->update([
            'round_status' => RoundStatus::Betting,
            'started_at'   => now(),
        ]);

        $endsAt = now()->addSeconds(config('game.betting_duration'))->timestamp;
        $this->redis->setTimerEndsAt($endsAt);
        $this->redis->updateRoundField('round_status', RoundStatus::Betting->value);
        $this->redis->updateRoundField('started_at', now()->toIso8601String());

        // Broadcast to all players via WebSocket
        // broadcast(new \App\Events\RoundStarted($round->id, config('game.betting_duration')));
        // Note: Uncomment when Events are set up in next module
    }

    // ─── BET ────────────────────────────────────────────────────

    /**
     * Place (or update) a player's bet on the current round.
     * Also starts the round if it was waiting.
     */
    public function placeBet(GameRound $round, Player $player, array $bets, int $totalBet): RoundBet
    {
        $bet = DB::transaction(function () use ($round, $player, $bets, $totalBet) {

            // If first bet, start the round
            if ($round->round_status === RoundStatus::Waiting) {
                $this->startBetting($round);
            }

            // Upsert — handles adding more chips to same bet type
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

            // Deduct balance in DB (source of truth)
            // Frontend does optimistic deduction for UX responsiveness
            // On round result, we sync back to correct value
            $player->decrement('balance', $totalBet);

            return $bet;
        });

        // Track in Redis for active player count
        $this->redis->addActivePlayer($player->id, $player->player_name);

        // Log to activity feed (every click)
        ActivityFeed::create([
            'round_id'     => $round->id,
            'player_id'    => $player->id,
            'player_name'  => $player->player_name,
            'activity_type'=> 'placed_bet',
            'amount'       => $totalBet,
            'message'      => "{$player->player_name} bet {$totalBet} WPUFF",
            'created_at'   => now(),
        ]);

        // Broadcast bet to all players (activity feed update)
        // broadcast(new \App\Events\BetPlaced(...));

        return $bet;
    }

    // ─── DEAL CARDS ─────────────────────────────────────────────

    /**
     * Deal cards, calculate results, pay out all bets.
     * Called automatically when timer expires.
     */
    public function dealCards(GameRound $round): void
    {
        // Guard: already dealt
        if ($round->hasCards()) {
            return;
        }

        [$playerCards, $bankerCards] = $this->deck->deal();

        $playerTotal  = $this->deck->calculateTotal($playerCards);
        $bankerTotal  = $this->deck->calculateTotal($bankerCards);
        $result       = $this->determineResult($playerTotal, $bankerTotal);
        $isPlayerPair = $this->deck->isPair($playerCards);
        $isBankerPair = $this->deck->isPair($bankerCards);
        $isRandomPair = $this->deck->isRandomPair($playerCards, $bankerCards); // Module 7

        // Save to DB
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
            'dealing_ends_at' => now()->addSeconds(config('game.dealing_duration')),
        ]);

        // Update Redis
        $this->redis->updateRoundField('round_status', RoundStatus::Dealing->value);
        $this->redis->updateRoundField('player_cards', $playerCards);
        $this->redis->updateRoundField('banker_cards', $bankerCards);
        $this->redis->updateRoundField('result', $result);

        // Broadcast cards to all players simultaneously
        // broadcast(new \App\Events\CardsDealt(...));

        // Pay out all bets
        $this->calculatePayouts($round->fresh());
    }

    // ─── PAYOUTS ────────────────────────────────────────────────

    private function calculatePayouts(GameRound $round): void
    {
        $bets = $round->bets()->with('player')->get();

        foreach ($bets as $bet) {
            $winnings  = $this->payouts->calculate($bet, $round);
            $netChange = $winnings - $bet->total_bet; // Can be negative (loss)

            DB::transaction(function () use ($bet, $winnings, $netChange) {
                $bet->update(['total_won' => $winnings]);

                // Balance was already decremented at bet time
                // Now add back winnings (0 if lost, full amount if won)
                if ($winnings > 0) {
                    $bet->player->increment('balance', $winnings);
                }

                $bet->player->increment('total_games_played');
                $bet->player->increment('total_winnings', $netChange);
            });

            // Log activity
            $this->logResult($round, $bet, $winnings, $netChange);

            // Private result to each player via WebSocket
            // broadcast(new \App\Events\PlayerResult(...));
        }

        // Mark finished
        $round->update([
            'round_status' => RoundStatus::Finished,
            'finished_at'  => now(),
        ]);

        $this->redis->updateRoundField('round_status', RoundStatus::Finished->value);
    }

    // ─── HELPERS ────────────────────────────────────────────────

    private function determineResult(int $playerTotal, int $bankerTotal): string
    {
        return match (true) {
            $playerTotal > $bankerTotal => 'PLAYER_WINS',
            $bankerTotal > $playerTotal => 'BANKER_WINS',
            default                     => 'TIE',
        };
    }

    private function logResult(GameRound $round, RoundBet $bet, int $winnings, int $netChange): void
    {
        [$type, $message] = match (true) {
            $netChange > 0 => [
                $netChange > $bet->total_bet * 2 ? 'big_win' : 'won',
                "{$bet->player_name} won {$netChange} WPUFF!",
            ],
            $netChange === 0 => [
                'returned',
                "{$bet->player_name} tied — bets returned",
            ],
            default => [
                'lost',
                "{$bet->player_name} lost " . abs($netChange) . " WPUFF",
            ],
        };

        ActivityFeed::create([
            'round_id'      => $round->id,
            'player_id'     => $bet->player_id,
            'player_name'   => $bet->player_name,
            'activity_type' => $type,
            'amount'        => abs($netChange),
            'message'       => $message,
            'created_at'    => now(),
        ]);
    }
}
