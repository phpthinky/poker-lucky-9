<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a round transitions from 'waiting' → 'betting'.
 * Tells all clients: start your timer, enable the bet boxes.
 *
 * Broadcast channel: game-table (public)
 * Frontend listener:  .listen('.round.started', ...)
 */
class RoundStarted implements ShouldBroadcastNow
{
    use SerializesModels;

    public function __construct(
        public readonly int    $roundId,
        public readonly int    $timerSeconds,
        public readonly string $status = 'betting',
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('game-table');
    }

    public function broadcastAs(): string
    {
        return 'round.started';
    }

    /**
     * Only broadcast these fields to the client.
     */
    public function broadcastWith(): array
    {
        return [
            'round_id'     => $this->roundId,
            'timer_seconds'=> $this->timerSeconds,
            'status'       => $this->status,
        ];
    }
}
