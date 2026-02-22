<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a round transitions:
 *   waiting → betting  (roundEndsAt = UTC ISO string, status = 'betting')
 *   any     → waiting  (roundEndsAt = null,            status = 'waiting')
 *
 * Frontend calculates the countdown from the authoritative timestamp:
 *   remaining = Math.max(0, (new Date(round_ends_at) - Date.now()) / 1000)
 * No server ticks needed.
 *
 * Broadcast channel: game-table (public)
 * Frontend listener:  .listen('.round.started', ...)
 */
class RoundStarted implements ShouldBroadcastNow
{
    use SerializesModels;

    public function __construct(
        public readonly int     $roundId,
        public readonly ?string $roundEndsAt,        // ISO 8601 UTC — null for waiting rounds
        public readonly string  $status = 'betting',
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('game-table');
    }

    public function broadcastAs(): string
    {
        return 'round.started';
    }

    public function broadcastWith(): array
    {
        return [
            'round_id'      => $this->roundId,
            'round_ends_at' => $this->roundEndsAt,   // null for waiting, ISO string for betting
            'status'        => $this->status,
        ];
    }
}
