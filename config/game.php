<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Game Timing (seconds)
    |--------------------------------------------------------------------------
    */
    'betting_duration'  => (int) env('GAME_BETTING_DURATION', 20),
    'dealing_duration'  => (int) env('GAME_DEALING_DURATION', 5),
    'result_duration'   => (int) env('GAME_RESULT_DURATION', 5),

    /*
    |--------------------------------------------------------------------------
    | Player Defaults
    |--------------------------------------------------------------------------
    */
    'starting_balance'  => (int) env('GAME_STARTING_BALANCE', 1000),
    'hourly_bonus'      => 10,   // WPUFF per hour away
    'max_bonus_hours'   => 5,    // cap at 50 WPUFF

    /*
    |--------------------------------------------------------------------------
    | Payouts (multiplier on bet amount returned)
    | e.g. player = 2 means bet 100, get back 200 (profit 100)
    |--------------------------------------------------------------------------
    */
    'payouts' => [
        'player'      => 2,   // 1:1
        'banker'      => 2,   // 1:1
        'tie'         => 8,   // 7:1
        'player_pair' => 11,  // 10:1
        'banker_pair' => 11,  // 10:1
        'random_pair' => 5,   // 4:1 (Module 7 — same card value across hands)
    ],

];
