<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Player extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'guest_id',
        'player_name',
        'email',
        'password',
        'avatar',
        'fcm_token',
        'balance',
        'total_games_played',
        'total_winnings',
        'last_visit',
    ];

    protected $hidden = [
        'password',
        'fcm_token',
        'remember_token',
    ];

    protected $casts = [
        'balance'             => 'integer',
        'total_games_played'  => 'integer',
        'total_winnings'      => 'integer',
        'last_visit'          => 'datetime',
        'email_verified_at'   => 'datetime',
        'password'            => 'hashed',
    ];

    // ─── RELATIONSHIPS ──────────────────────────────────────────

    public function bets()
    {
        return $this->hasMany(RoundBet::class, 'player_id');
    }

    public function bonusClaims()
    {
        return $this->hasMany(BonusClaim::class, 'player_id');
    }

    public function activities()
    {
        return $this->hasMany(ActivityFeed::class, 'player_id');
    }

    // ─── HOURLY BONUS ───────────────────────────────────────────

    /**
     * Calculate how much hourly bonus this player is owed.
     */
    public function calculateHourlyBonus(): int
    {
        if (! $this->last_visit) {
            return 0;
        }

        $hoursPassed  = now()->diffInHours($this->last_visit);
        $hoursToReward = min($hoursPassed, config('game.max_bonus_hours', 5));

        return $hoursToReward * config('game.hourly_bonus', 10);
    }

    // ─── SCOPES ─────────────────────────────────────────────────

    public function scopeLeaderboard($query, int $limit = 10)
    {
        return $query->orderByDesc('balance')->limit($limit);
    }
}
