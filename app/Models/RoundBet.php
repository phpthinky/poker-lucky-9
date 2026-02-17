<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoundBet extends Model
{
    protected $fillable = [
        'round_id',
        'player_id',
        'player_name',
        'bet_player',
        'bet_banker',
        'bet_tie',
        'bet_player_pair',
        'bet_banker_pair',
        'bet_random_pair',
        'total_bet',
        'total_won',
    ];

    protected $casts = [
        'bet_player'      => 'integer',
        'bet_banker'      => 'integer',
        'bet_tie'         => 'integer',
        'bet_player_pair' => 'integer',
        'bet_banker_pair' => 'integer',
        'bet_random_pair' => 'integer',
        'total_bet'       => 'integer',
        'total_won'       => 'integer',
    ];

    // ─── RELATIONSHIPS ──────────────────────────────────────────

    public function round()
    {
        return $this->belongsTo(GameRound::class, 'round_id');
    }

    public function player()
    {
        return $this->belongsTo(Player::class, 'player_id');
    }

    // ─── HELPERS ────────────────────────────────────────────────

    /**
     * Net change to balance (positive = profit, negative = loss).
     */
    public function getNetChangeAttribute(): int
    {
        return $this->total_won - $this->total_bet;
    }

    /**
     * Whether this player won this round.
     */
    public function isWinner(): bool
    {
        return $this->total_won > $this->total_bet;
    }

    /**
     * Build bet array from individual fields (for service use).
     */
    public function toBetsArray(): array
    {
        return [
            'player'      => $this->bet_player,
            'banker'      => $this->bet_banker,
            'tie'         => $this->bet_tie,
            'playerPair'  => $this->bet_player_pair,
            'bankerPair'  => $this->bet_banker_pair,
            'randomPair'  => $this->bet_random_pair,
        ];
    }
}
