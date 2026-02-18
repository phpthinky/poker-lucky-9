<?php

namespace App\Http\Controllers;

use App\Models\ActivityFeed;
use Carbon\Carbon;
use App\Models\GameRound;
use App\Models\Player;
use App\Models\RoundBet;
use App\Models\RoundStatus;
use App\Services\GameEngineService;
use App\Services\RedisStateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GameController extends Controller
{
    public function __construct(
        private readonly GameEngineService $engine,
        private readonly RedisStateService $redis,
    ) {}

    // ─── GET FULL GAME STATE ─────────────────────────────────────

    /**
     * Returns everything the frontend needs in one call.
     * Redis-first for the round, DB for player-specific data.
     *
     * GET /api/v1/game/state
     */
    public function state(Request $request): JsonResponse
    {
        // Round: Redis first (fast), fall back to DB
        $roundData = $this->redis->getRound();

        if (! $roundData) {
            $round     = GameRound::latest()->first();
            $roundData = $round ? $this->formatRound($round) : null;
        }

        // Timer: Redis stores the precise countdown.
        // If Redis is down, recalculate from DB started_at timestamp.
        if ($roundData) {
            $timerFromRedis = $this->redis->getTimerRemaining();

            if ($timerFromRedis > 0) {
                $roundData['timer_remaining'] = $timerFromRedis;
            } elseif (
                ($roundData['round_status'] ?? '') === 'betting' &&
                ! empty($roundData['started_at'])
            ) {
                // Redis unavailable — compute from DB timestamp
                $elapsed  = now()->diffInSeconds(\Carbon\Carbon::parse($roundData['started_at']));
                $duration = config('game.betting_duration', 20);
                $roundData['timer_remaining'] = max(0, $duration - $elapsed);
            } else {
                $roundData['timer_remaining'] = 0;
            }
        }

        // Player's bet on this round
        $playerBet = null;
        if ($request->user() && $roundData) {
            $playerBet = RoundBet::where([
                'round_id'  => $roundData['id'],
                'player_id' => $request->user()->id,
            ])->first();
        }

        // Recent activity
        $recentActivity = ActivityFeed::recent(5)->get();

        // Leaderboard
        $leaderboard = Player::leaderboard(5)->get([
            'id', 'player_name', 'balance',
        ]);

        // Active players: Redis first, fall back to counting DB bets for current round
        $activePlayers = $this->redis->getActivePlayers();

        if (empty($activePlayers) && $roundData) {
            // Redis unavailable — count who has a bet in the current round
            $activePlayers = RoundBet::where('round_id', $roundData['id'])
                ->pluck('player_name', 'player_id')
                ->toArray();
        }

        return response()->json([
            'success'         => true,
            'current_round'   => $roundData,
            'player_bet'      => $playerBet,
            'active_players'  => $activePlayers,
            'recent_activity' => $recentActivity,
            'leaderboard'     => $leaderboard,
            'timestamp'       => now()->timestamp,
        ]);
    }

    // ─── PLACE BET ───────────────────────────────────────────────

    /**
     * POST /api/v1/game/bet
     * Body: {
     *   round_id: 5,
     *   bets: { player: 100, banker: 0, tie: 0, playerPair: 0, bankerPair: 0, randomPair: 0 }
     * }
     */
    public function placeBet(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'round_id'          => 'required|integer|exists:game_rounds,id',
            'bets'              => 'required|array',
            'bets.player'       => 'integer|min:0',
            'bets.banker'       => 'integer|min:0',
            'bets.tie'          => 'integer|min:0',
            'bets.playerPair'   => 'integer|min:0',
            'bets.bankerPair'   => 'integer|min:0',
            'bets.randomPair'   => 'integer|min:0',
        ]);

        $bets      = $validated['bets'];
        $player    = $request->user();
        $totalBet  = array_sum($bets);

        // ── Validations ──────────────────────────────────────────

        if ($totalBet <= 0) {
            return $this->error('Bet amount must be greater than 0', 422);
        }

        if (($bets['player'] ?? 0) > 0 && ($bets['banker'] ?? 0) > 0) {
            return $this->error('Cannot bet both Player and Banker', 422);
        }

        // Re-fetch live balance from DB (not from frontend — never trust client balance)
        $player->refresh();
        if ($player->balance < $totalBet) {
            return $this->error('Insufficient balance', 422);
        }

        // ── Round check ──────────────────────────────────────────

        $round = GameRound::findOrFail($validated['round_id']);

        if (! $round->round_status->canBet()) {
            return $this->error('Betting is closed for this round', 422);
        }

        // ── Place bet via engine ─────────────────────────────────

        $bet = $this->engine->placeBet(
            round:    $round,
            player:   $player,
            bets:     $bets,
            totalBet: $totalBet,
        );

        return response()->json([
            'success' => true,
            'message' => 'Bet placed!',
            'bet'     => $bet,
            'balance' => $player->fresh()->balance,
        ]);
    }

    // ─── CLEAR BET ───────────────────────────────────────────────

    /**
     * Refund current round bet (only during betting phase).
     *
     * POST /api/v1/game/clear
     * Body: { round_id: 5 }
     */
    public function clearBet(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'round_id' => 'required|integer|exists:game_rounds,id',
        ]);

        $player = $request->user();
        $round  = GameRound::findOrFail($validated['round_id']);

        if ($round->round_status !== RoundStatus::Betting) {
            return $this->error('Can only clear bets during betting phase', 422);
        }

        $bet = RoundBet::where([
            'round_id'  => $round->id,
            'player_id' => $player->id,
        ])->first();

        if (! $bet) {
            return $this->error('No bet found for this round', 404);
        }

        DB::transaction(function () use ($bet, $player) {
            $player->increment('balance', $bet->total_bet);  // Refund
            $bet->delete();
        });

        return response()->json([
            'success' => true,
            'message' => "Bets cleared! {$bet->total_bet} WPUFF refunded.",
            'balance' => $player->fresh()->balance,
        ]);
    }

    // ─── PLAYER PROFILE ──────────────────────────────────────────

    /**
     * GET /api/v1/player
     */
    public function profile(Request $request): JsonResponse
    {
        $player = $request->user();

        return response()->json([
            'success' => true,
            'player'  => [
                'id'                  => $player->id,
                'player_name'         => $player->player_name,
                'balance'             => $player->balance,
                'total_games_played'  => $player->total_games_played,
                'total_winnings'      => $player->total_winnings,
                'last_visit'          => $player->last_visit,
            ],
        ]);
    }

    // ─── RESET BALANCE ───────────────────────────────────────────

    /**
     * POST /api/v1/player/reset
     */
    public function resetBalance(Request $request): JsonResponse
    {
        $request->user()->update(['balance' => config('game.starting_balance')]);

        return response()->json([
            'success' => true,
            'message' => 'Balance reset to ' . config('game.starting_balance') . ' WPUFF',
            'balance' => config('game.starting_balance'),
        ]);
    }

    // ─── HELPERS ────────────────────────────────────────────────

    private function formatRound(GameRound $round): array
    {
        return array_merge($round->toArray(), [
            'timer_remaining' => $round->timer_remaining,
        ]);
    }

    private function error(string $message, int $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }
}
