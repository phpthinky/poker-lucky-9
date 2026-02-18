<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

/**
 * Fired every second during the betting phase by ProcessRoundTimer job.
 * Replaces client-side polling entirely — server pushes the countdown.
 *
 * Broadcast channel: game-table (public)
 * Frontend listener:  .listen('.timer.tick', ...)
 */
class TimerTick implements ShouldBroadcast
{
    public function __construct(
        public readonly int $roundId,
        public readonly int $secondsRemaining,
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('game-table');
    }

    public function broadcastAs(): string
    {
        return 'timer.tick';
    }

    public function broadcastWith(): array
    {
        return [
            'round_id'          => $this->roundId,
            'seconds_remaining' => $this->secondsRemaining,
        ];
    }
}
