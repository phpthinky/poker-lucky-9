<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

/**
 * Fired after all payouts are calculated and round is marked 'finished'.
 * Non-betting players use this to close their dealing overlay.
 * All clients start their "next round in X seconds" countdown.
 *
 * Broadcast channel: game-table (public)
 * Frontend listener:  .listen('.round.finished', ...)
 */
class RoundFinished implements ShouldBroadcastNow
{
    public function __construct(
        public readonly int    $roundId,
        public readonly string $result,
        public readonly int    $playerTotal,
        public readonly int    $bankerTotal,
        public readonly int    $nextRoundIn,  // Seconds until next round
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('game-table');
    }

    public function broadcastAs(): string
    {
        return 'round.finished';
    }

    public function broadcastWith(): array
    {
        return [
            'round_id'     => $this->roundId,
            'result'       => $this->result,
            'player_total' => $this->playerTotal,
            'banker_total' => $this->bankerTotal,
            'next_round_in'=> $this->nextRoundIn,
        ];
    }
}
