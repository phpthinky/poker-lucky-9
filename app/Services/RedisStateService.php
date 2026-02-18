<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * RedisStateService — Redis-optional game state cache.
 *
 * Every Redis call is wrapped in try/catch.
 * If Redis is unavailable (Hostinger shared, no Redis installed, etc.)
 * the game continues normally — it just reads from DB instead of cache.
 *
 * Redis is a performance optimisation, NOT a requirement.
 * All authoritative data lives in MySQL.
 */
class RedisStateService
{
    private const ROUND_KEY   = 'lucky_puffin:round';
    private const TIMER_KEY   = 'lucky_puffin:timer_ends_at';
    private const PLAYERS_KEY = 'lucky_puffin:active_players';
    private const TTL         = 3600;

    /** Cached per-request so we only ping once */
    private static ?bool $available  = null;

    // ── Connection check ──────────────────────────────────────────────────────

    private function up(): bool
    {
        if (self::$available !== null) {
            return self::$available;
        }

        try {
            Redis::ping();
            self::$available = true;
        } catch (\Throwable) {
            self::$available = false;
            Log::info('[Redis] Unavailable — game running without cache (DB is source of truth)');
        }

        return self::$available;
    }

    /**
     * Execute a Redis closure safely.
     * Returns $fallback if Redis is down or the call throws.
     */
    private function try(callable $fn, mixed $fallback = null): mixed
    {
        if (! $this->up()) {
            return $fallback;
        }

        try {
            return $fn();
        } catch (\Throwable $e) {
            self::$available = false;
            Log::warning('[Redis] Call failed mid-request: ' . $e->getMessage());
            return $fallback;
        }
    }

    // ── Round state ───────────────────────────────────────────────────────────

    public function setRound(array $data): void
    {
        $this->try(fn () => Redis::setex(self::ROUND_KEY, self::TTL, json_encode($data)));
    }

    public function getRound(): ?array
    {
        $raw = $this->try(fn () => Redis::get(self::ROUND_KEY));
        return $raw ? json_decode($raw, true) : null;
    }

    public function updateRoundField(string $field, mixed $value): void
    {
        $round          = $this->getRound() ?? [];
        $round[$field]  = $value;
        $this->setRound($round);
    }

    public function clearRound(): void
    {
        $this->try(fn () => Redis::del(self::ROUND_KEY));
    }

    // ── Timer ─────────────────────────────────────────────────────────────────

    public function setTimerEndsAt(int $unixTimestamp): void
    {
        $this->try(fn () => Redis::setex(self::TIMER_KEY, self::TTL, $unixTimestamp));
    }

    /**
     * Seconds remaining in the betting phase.
     * Returns 0 when Redis is down — GameController recalculates from DB started_at.
     */
    public function getTimerRemaining(): int
    {
        $endsAt = $this->try(fn () => Redis::get(self::TIMER_KEY));
        if (! $endsAt) return 0;
        return max(0, (int) $endsAt - time());
    }

    public function clearTimer(): void
    {
        $this->try(fn () => Redis::del(self::TIMER_KEY));
    }

    // ── Active players ────────────────────────────────────────────────────────

    public function addActivePlayer(int $playerId, string $playerName): void
    {
        $this->try(function () use ($playerId, $playerName) {
            Redis::hset(self::PLAYERS_KEY, $playerId, $playerName);
            Redis::expire(self::PLAYERS_KEY, self::TTL);
        });
    }

    public function removeActivePlayer(int $playerId): void
    {
        $this->try(fn () => Redis::hdel(self::PLAYERS_KEY, $playerId));
    }

    /**
     * Returns {playerId: playerName} or empty array when Redis is down.
     */
    public function getActivePlayers(): array
    {
        return $this->try(fn () => Redis::hgetall(self::PLAYERS_KEY) ?? [], []);
    }

    public function clearActivePlayers(): void
    {
        $this->try(fn () => Redis::del(self::PLAYERS_KEY));
    }

    // ── Full reset ────────────────────────────────────────────────────────────

    public function flushGameState(): void
    {
        $this->try(fn () => Redis::del([self::ROUND_KEY, self::TIMER_KEY, self::PLAYERS_KEY]));
    }
}
