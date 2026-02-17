<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Guest login — create or retrieve player by browser ID.
     * This mirrors what the old PHP system did with localStorage guest_id.
     *
     * POST /api/v1/guest/login
     * Body: { guest_id: "guest_123_4567", player_name?: "Guest1234" }
     */
    public function guestLogin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'guest_id'    => 'required|string|max:100',
            'player_name' => 'nullable|string|max:50',
            'fcm_token'   => 'nullable|string',             // Module 9
        ]);

        $player = DB::transaction(function () use ($validated) {

            $player = Player::firstOrCreate(
                ['guest_id' => $validated['guest_id']],
                [
                    'player_name' => $validated['player_name'] ?? 'Guest' . rand(1000, 9999),
                    'balance'     => config('game.starting_balance'),
                    'last_visit'  => now(),
                ]
            );

            // Apply hourly bonus on return visit
            $bonus = $player->calculateHourlyBonus();
            if ($bonus > 0) {
                $player->increment('balance', $bonus);
                $player->bonusClaims()->create(['bonus_amount' => $bonus, 'claimed_at' => now()]);
                $player->bonus_earned = $bonus;
            }

            // Update FCM token if provided (Module 9)
            $updates = ['last_visit' => now()];
            if (! empty($validated['fcm_token'])) {
                $updates['fcm_token'] = $validated['fcm_token'];
            }
            $player->update($updates);

            return $player->fresh();
        });

        // Sanctum token for subsequent API requests
        $token = $player->createToken('guest-session')->plainTextToken;

        return response()->json([
            'success' => true,
            'token'   => $token,
            'player'  => [
                'id'           => $player->id,
                'guest_id'     => $player->guest_id,
                'player_name'  => $player->player_name,
                'balance'      => $player->balance,
                'bonus_earned' => $player->bonus_earned ?? 0,
                'is_new'       => $player->wasRecentlyCreated,
            ],
            'message' => $this->welcomeMessage($player),
        ]);
    }

    /**
     * Full registration (future — Module 9 / mobile).
     *
     * POST /api/v1/register
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'player_name' => 'required|string|max:50',
            'email'       => 'required|email|unique:players,email',
            'password'    => 'required|string|min:8|confirmed',
            'guest_id'    => 'nullable|string|max:100',
        ]);

        // If they had a guest account, upgrade it
        $player = null;
        if (! empty($validated['guest_id'])) {
            $player = Player::where('guest_id', $validated['guest_id'])->first();
        }

        if ($player) {
            $player->update([
                'player_name' => $validated['player_name'],
                'email'       => $validated['email'],
                'password'    => Hash::make($validated['password']),
            ]);
        } else {
            $player = Player::create([
                'guest_id'    => 'user_' . uniqid(),
                'player_name' => $validated['player_name'],
                'email'       => $validated['email'],
                'password'    => Hash::make($validated['password']),
                'balance'     => config('game.starting_balance'),
                'last_visit'  => now(),
            ]);
        }

        $token = $player->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token'   => $token,
            'player'  => $player->only(['id', 'player_name', 'email', 'balance']),
        ], 201);
    }

    /**
     * Email/password login (future).
     *
     * POST /api/v1/login
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $player = Player::where('email', $validated['email'])->first();

        if (! $player || ! Hash::check($validated['password'], $player->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        $player->update(['last_visit' => now()]);

        $token = $player->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token'   => $token,
            'player'  => $player->only(['id', 'player_name', 'email', 'balance']),
        ]);
    }

    /**
     * Logout — revoke current token.
     *
     * POST /api/v1/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['success' => true, 'message' => 'Logged out']);
    }

    // ─── PRIVATE ────────────────────────────────────────────────

    private function welcomeMessage(Player $player): string
    {
        if ($player->wasRecentlyCreated) {
            return "Welcome {$player->player_name}! You start with " . config('game.starting_balance') . " WPUFF.";
        }

        if (! empty($player->bonus_earned)) {
            $hours = intdiv($player->bonus_earned, config('game.hourly_bonus'));
            return "Welcome back! You earned {$player->bonus_earned} bonus WPUFF for being away {$hours} hour(s)!";
        }

        return "Welcome back {$player->player_name}!";
    }
}
