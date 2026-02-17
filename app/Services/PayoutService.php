<?php

namespace App\Services;

use App\Models\GameRound;
use App\Models\RoundBet;
use App\Models\RoundStatus;

class PayoutService
{
    /**
     * Calculate total winnings for a given bet on a finished round.
     *
     * Returns the TOTAL amount the player gets back (stake + profit).
     * Returns 0 if they lost everything.
     *
     * Balance flow:
     *   - placeBet():     balance -= total_bet   (deducted up front)
     *   - calculatePayout: balance += winnings   (added back on result)
     *   - Net on loss:    -total_bet
     *   - Net on win:     +profit (winnings - total_bet)
     */
    public function calculate(RoundBet $bet, GameRound $round): int
    {
        $p        = config('game.payouts');
        $winnings = 0;

        // ── Side bets (resolved independently of main result) ────

        if ($round->is_player_pair && $bet->bet_player_pair > 0) {
            $winnings += $bet->bet_player_pair * $p['player_pair']; // 11x
        }

        if ($round->is_banker_pair && $bet->bet_banker_pair > 0) {
            $winnings += $bet->bet_banker_pair * $p['banker_pair']; // 11x
        }

        // Module 7 — Random Pair bonus (5x)
        if ($round->is_random_pair && $bet->bet_random_pair > 0) {
            $winnings += $bet->bet_random_pair * $p['random_pair']; // 5x
        }

        // ── Main bet ─────────────────────────────────────────────

        $winnings += match ($round->result) {
            'PLAYER_WINS' => $bet->bet_player * $p['player'],   // 2x stake back
            'BANKER_WINS' => $bet->bet_banker * $p['banker'],   // 2x stake back
            'TIE'         => ($bet->bet_tie * $p['tie'])        // 8x tie bet
                           + $bet->bet_player                   // Player/Banker
                           + $bet->bet_banker,                  //   returned on tie
            default       => 0,
        };

        return $winnings;
    }
}
