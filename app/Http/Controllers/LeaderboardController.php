<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Services\RedisStateService;
use Illuminate\Http\JsonResponse;

class LeaderboardController extends Controller
{
    public function __construct(
        private readonly RedisStateService $redis,
    ) {}

    /**
     * GET /api/v1/leaderboard
     */
    public function index(): JsonResponse
    {
        $activePlayers = array_keys($this->redis->getActivePlayers());

        $leaderboard = Player::leaderboard(10)
            ->get(['id', 'player_name', 'balance', 'total_games_played', 'total_winnings'])
            ->map(fn ($p) => array_merge($p->toArray(), [
                'is_playing' => in_array($p->id, $activePlayers),
            ]));

        return response()->json([
            'success'     => true,
            'leaderboard' => $leaderboard,
        ]);
    }
}
