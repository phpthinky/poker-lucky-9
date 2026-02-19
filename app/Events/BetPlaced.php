<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

/**
 * Fired whenever any player adds chips (every click).
 * All other players see this in their activity feed in real time.
 *
 * Broadcast channel: game-table (public)
 * Frontend listener:  .listen('.bet.placed', ...)
 */
class BetPlaced implements ShouldBroadcastNow
{
    public function __construct(
        public readonly int    $roundId,
        public readonly string $playerName,
        public readonly int    $totalBet,    // Player's cumulative bet this round
        public readonly int    $activePlayers,
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('game-table');
    }

    public function broadcastAs(): string
    {
        return 'bet.placed';
    }

    public function broadcastWith(): array
    {
        return [
            'round_id'       => $this->roundId,
            'player_name'    => $this->playerName,
            'total_bet'      => $this->totalBet,
            'active_players' => $this->activePlayers,
        ];
    }
}
