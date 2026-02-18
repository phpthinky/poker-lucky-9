<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

/**
 * Fired once per player after payouts are calculated.
 * Goes on a PRIVATE channel so each player only sees their own result.
 *
 * Channel:          player.{playerId}  (private — auth required)
 * Frontend listener: Echo.private(`player.${playerId}`).listen('.round.result', ...)
 */
class PlayerResult implements ShouldBroadcast
{
    public function __construct(
        public readonly int    $playerId,
        public readonly string $result,       // PLAYER_WINS | BANKER_WINS | TIE
        public readonly int    $totalBet,
        public readonly int    $totalWon,
        public readonly int    $profit,       // totalWon - totalBet (negative = loss)
        public readonly int    $newBalance,   // Authoritative balance from DB
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        // Private channel — only this player's Echo.private() subscription receives it
        return new PrivateChannel("player.{$this->playerId}");
    }

    public function broadcastAs(): string
    {
        return 'round.result';
    }

    public function broadcastWith(): array
    {
        return [
            'result'      => $this->result,
            'total_bet'   => $this->totalBet,
            'total_won'   => $this->totalWon,
            'profit'      => $this->profit,
            'new_balance' => $this->newBalance,
        ];
    }
}
