<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\LeaderboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Lucky Puffin
|--------------------------------------------------------------------------
| All routes prefixed with /api/v1
| Sanctum token required for protected routes (passed as Bearer token)
|
| Headers:
|   Content-Type: application/json
|   Accept: application/json
|   Authorization: Bearer {token}   ← protected routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // ── Public: Auth ─────────────────────────────────────────────
    // No token required — used on first load to identify player

    Route::post('/guest/login', [AuthController::class, 'guestLogin']);
    Route::post('/register',   [AuthController::class, 'register']);
    Route::post('/login',      [AuthController::class, 'login']);

    // ── Public: Leaderboard (viewable without login) ──────────────
    Route::get('/leaderboard', [LeaderboardController::class, 'index']);

    // ── Public: Game state (no login needed — auth is optional inside controller) ──
    Route::get('/game/state', [GameController::class, 'state']);

    // ── Protected: Require Sanctum token ─────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::post('/logout', [AuthController::class, 'logout']);

        // Game (bet & clear still require auth so we know who placed the bet)
        Route::post('/game/bet',       [GameController::class, 'placeBet']);
        Route::post('/game/clear',     [GameController::class, 'clearBet']);

        // Player
        Route::get('/player',          [GameController::class, 'profile']);
        Route::post('/player/reset',   [GameController::class, 'resetBalance']);

        // ── Future: Community (Module 8) ─────────────────────────
        // Route::apiResource('posts', CommunityController::class);
        // Route::post('posts/{post}/reply', [CommunityController::class, 'reply']);

        // ── Future: Mobile push token (Module 9) ─────────────────
        // Route::post('/player/fcm', [AuthController::class, 'updateFcmToken']);
    });
});
