<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityFeed extends Model
{
    public $timestamps = false;  // Only has created_at

    protected $fillable = [
        'round_id',
        'player_id',
        'player_name',
        'activity_type',
        'amount',
        'message',
        'created_at',
    ];

    protected $casts = [
        'amount'     => 'integer',
        'created_at' => 'datetime',
    ];

    public function round()
    {
        return $this->belongsTo(GameRound::class, 'round_id');
    }

    public function player()
    {
        return $this->belongsTo(Player::class, 'player_id');
    }

    // ─── SCOPES ─────────────────────────────────────────────────

    public function scopeRecent($query, int $limit = 10)
    {
        return $query->latest('created_at')->limit($limit);
    }
}
