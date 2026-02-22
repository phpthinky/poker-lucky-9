<?php

namespace App\Http\Controllers;

use App\Models\ActivityFeed;
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
     * Always reads from DB — no Redis round cache.
     *
     * Also acts as the dealing trigger: if the current round's betting
     * period has expired, this request atomically transitions it to dealing,
     * runs payouts, and starts the next waiting round — all inline.
     *
     * GET /api/v1/game/state
     */
    public function state(Request $request): JsonResponse
    {
        // Always prefer an active round (waiting/betting/dealing) over finished.
        $round = GameRound::active()->latest()->first()
              ?? GameRound::latest()->first();

        if (! $round) {
            $round = $this->engine->createWaitingRound();
        }

        // ── Atomic dealing trigger ──────────────────────────────────────────
        // First request after round_ends_at wins the race. All others get the
        // already-transitioned round. No cron, no queue, no race condition.
        if ($round->round_status === RoundStatus::Betting
            && $round->round_ends_at
            && now()->gte($round->round_ends_at)
        ) {
            $this->engine->attemptDeal($round);
            // Reload — round is now dealing/finished and a new waiting round may exist
            $round = GameRound::active()->latest()->first()
                  ?? GameRound::latest()->first();
        }

        $roundData = $this->formatRound($round);

        // Player's bet on this round — auth is optional on this public route
        $authedUser = auth('sanctum')->user();
        $playerBet  = null;
        if ($authedUser) {
            $playerBet = RoundBet::where([
                'round_id'  => $round->id,
                'player_id' => $authedUser->id,
            ])->first();
        }

        // Active players: cache first, fall back to DB bets
        $activePlayers = $this->redis->getActivePlayers();
        if (empty($activePlayers)) {
            $activePlayers = RoundBet::where('round_id', $round->id)
                ->pluck('player_name', 'player_id')
                ->toArray();
        }

        return response()->json([
            'success'         => true,
            'current_round'   => $roundData,
            'player_bet'      => $playerBet,
            'player_balance'  => $authedUser ? $authedUser->fresh()->balance : null,
            'active_players'  => $activePlayers,
            'recent_activity' => ActivityFeed::recent(5)->get(),
            'leaderboard'     => Player::leaderboard(5)->get(['id', 'player_name', 'balance']),
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

        // If the betting window just closed, reject and trigger dealing immediately.
        if ($round->round_status === RoundStatus::Betting
            && $round->round_ends_at
            && now()->gte($round->round_ends_at)
        ) {
            $this->engine->attemptDeal($round);
            return $this->error('Betting time is up — cards are being dealt', 422);
        }

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

        // Refresh so we have round_ends_at set by startBetting().
        $round->refresh();

        return response()->json([
            'success'        => true,
            'message'        => 'Bet placed!',
            'bet'            => $bet,
            'balance'        => $player->fresh()->balance,
            'round_status'   => $round->round_status->value,
            // ISO 8601 UTC — frontend calculates remaining = (new Date(round_ends_at) - Date.now()) / 1000
            'round_ends_at'  => $round->round_ends_at?->toIso8601String(),
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
            // ISO 8601 UTC timestamp — frontend calculates countdown from this
            'round_ends_at'  => $round->round_ends_at?->toIso8601String(),
            // Seconds remaining — convenience alias (computed from round_ends_at)
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
