<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

/**
 * Fired once when cards are dealt (timer hits 0).
 * ALL players receive the same cards at the exact same millisecond.
 * This is the key advantage over polling — perfect sync.
 *
 * Broadcast channel: game-table (public)
 * Frontend listener:  .listen('.cards.dealt', ...)
 */
class CardsDealt implements ShouldBroadcast
{
    public function __construct(
        public readonly int    $roundId,
        public readonly array  $playerCards,
        public readonly array  $bankerCards,
        public readonly int    $playerTotal,
        public readonly int    $bankerTotal,
        public readonly string $result,
        public readonly bool   $isPlayerPair,
        public readonly bool   $isBankerPair,
        public readonly bool   $isRandomPair,  // Module 7
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('game-table');
    }

    public function broadcastAs(): string
    {
        return 'cards.dealt';
    }

    public function broadcastWith(): array
    {
        return [
            'round_id'       => $this->roundId,
            'player_cards'   => $this->playerCards,
            'banker_cards'   => $this->bankerCards,
            'player_total'   => $this->playerTotal,
            'banker_total'   => $this->bankerTotal,
            'result'         => $this->result,
            'is_player_pair' => $this->isPlayerPair,
            'is_banker_pair' => $this->isBankerPair,
            'is_random_pair' => $this->isRandomPair,
        ];
    }
}
