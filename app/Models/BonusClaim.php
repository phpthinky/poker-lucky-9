<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BonusClaim extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'player_id',
        'bonus_amount',
        'claimed_at',
    ];

    protected $casts = [
        'bonus_amount' => 'integer',
        'claimed_at'   => 'datetime',
    ];

    public function player()
    {
        return $this->belongsTo(Player::class, 'player_id');
    }
}
