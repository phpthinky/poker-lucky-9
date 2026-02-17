<?php

namespace App\Services;

class DeckService
{
    private const SUITS  = ['♥', '♦', '♣', '♠'];
    private const VALUES = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];

    /**
     * Deal a full hand: 2 cards each, draw third if total ≤ 5.
     * Returns [playerCards, bankerCards].
     */
    public function deal(): array
    {
        $deck = $this->createDeck();
        shuffle($deck);

        $playerCards = [array_pop($deck), array_pop($deck)];
        $bankerCards = [array_pop($deck), array_pop($deck)];

        if ($this->calculateTotal($playerCards) <= 5) {
            $playerCards[] = array_pop($deck);
        }

        if ($this->calculateTotal($bankerCards) <= 5) {
            $bankerCards[] = array_pop($deck);
        }

        return [$playerCards, $bankerCards];
    }

    /**
     * Lucky 9 total: sum of card values mod 10.
     */
    public function calculateTotal(array $cards): int
    {
        $total = array_reduce($cards, function (int $carry, array $card): int {
            return $carry + match ($card['value']) {
                'A'                      => 1,
                'J', 'Q', 'K', '10'     => 0,
                default                  => (int) $card['value'],
            };
        }, 0);

        return $total % 10;
    }

    /**
     * Player/Banker pair: first two cards share same value.
     */
    public function isPair(array $cards): bool
    {
        return count($cards) >= 2
            && $cards[0]['value'] === $cards[1]['value'];
    }

    /**
     * Module 7 — Random Pair: any player card value matches any banker card value.
     * Example: Player 7♥, Banker 7♦ → true
     */
    public function isRandomPair(array $playerCards, array $bankerCards): bool
    {
        $playerValues = array_column($playerCards, 'value');
        $bankerValues = array_column($bankerCards, 'value');

        return count(array_intersect($playerValues, $bankerValues)) > 0;
    }

    private function createDeck(): array
    {
        $deck = [];
        foreach (self::SUITS as $suit) {
            foreach (self::VALUES as $value) {
                $deck[] = [
                    'suit'    => $suit,
                    'value'   => $value,
                    'display' => $value . $suit,
                ];
            }
        }
        return $deck;
    }
}
