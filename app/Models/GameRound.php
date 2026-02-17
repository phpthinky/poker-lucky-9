<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// PHP 8.1+ backed enum for type-safe round status
enum RoundStatus: string
{
    case Waiting  = 'waiting';
    case Betting  = 'betting';
    case Dealing  = 'dealing';
    case Finished = 'finished';

    public function label(): string
    {
        return match($this) {
            self::Waiting  => 'Waiting for players...',
            self::Betting  => 'Place your bets!',
            self::Dealing  => 'Dealing cards...',
            self::Finished => 'Round complete',
        };
    }

    public function canBet(): bool
    {
        return in_array($this, [self::Waiting, self::Betting]);
    }
}

class GameRound extends Model
{
    use HasFactory;

    protected $fillable = [
        'round_status',
        'player_cards',
        'banker_cards',
        'player_total',
        'banker_total',
        'result',
        'is_player_pair',
        'is_banker_pair',
        'is_random_pair',
        'started_at',
        'dealing_ends_at',
        'finished_at',
    ];

    protected $casts = [
        'player_cards'    => 'array',
        'banker_cards'    => 'array',
        'player_total'    => 'integer',
        'banker_total'    => 'integer',
        'is_player_pair'  => 'boolean',
        'is_banker_pair'  => 'boolean',
        'is_random_pair'  => 'boolean',
        'round_status'    => RoundStatus::class,
        'started_at'      => 'datetime',
        'dealing_ends_at' => 'datetime',
        'finished_at'     => 'datetime',
    ];

    // ─── RELATIONSHIPS ──────────────────────────────────────────

    public function bets()
    {
        return $this->hasMany(RoundBet::class, 'round_id');
    }

    public function activities()
    {
        return $this->hasMany(ActivityFeed::class, 'round_id');
    }

    // ─── COMPUTED ATTRIBUTES ────────────────────────────────────

    /**
     * Seconds remaining in betting phase (calculated from timestamp, not stored).
     */
    public function getTimerRemainingAttribute(): int
    {
        if ($this->round_status !== RoundStatus::Betting || ! $this->started_at) {
            return 0;
        }

        $elapsed = now()->diffInSeconds($this->started_at, absolute: true);

        return max(0, config('game.betting_duration') - $elapsed);
    }

    /**
     * Seconds remaining in dealing display phase.
     */
    public function getDealingTimeRemainingAttribute(): int
    {
        if ($this->round_status !== RoundStatus::Dealing || ! $this->dealing_ends_at) {
            return 0;
        }

        return max(0, now()->diffInSeconds($this->dealing_ends_at, absolute: false));
    }

    // ─── SCOPES ─────────────────────────────────────────────────

    public function scopeLatestRound($query)
    {
        return $query->latest()->first();
    }

    public function scopeActive($query)
    {
        return $query->whereIn('round_status', [
            RoundStatus::Waiting->value,
            RoundStatus::Betting->value,
            RoundStatus::Dealing->value,
        ]);
    }

    // ─── HELPERS ────────────────────────────────────────────────

    public function hasCards(): bool
    {
        return ! empty($this->player_cards) && ! empty($this->banker_cards);
    }

    public function isStuck(): bool
    {
        // Stuck = dealing status but no cards
        return $this->round_status === RoundStatus::Dealing && ! $this->hasCards();
    }
}
