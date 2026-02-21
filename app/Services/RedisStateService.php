<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * RedisStateService — game state cache backed by Laravel Cache (database driver).
 *
 * Previously used Redis directly; now uses the Cache facade so any
 * Laravel cache driver works (database, file, array, etc.).
 * All authoritative data still lives in MySQL.
 */
class RedisStateService
{
    private const ROUND_KEY   = 'lucky_puffin:round';
    private const TIMER_KEY   = 'lucky_puffin:timer_ends_at';
    private const PLAYERS_KEY = 'lucky_puffin:active_players';
    private const TTL         = 3600;

    // ── Round state ───────────────────────────────────────────────────────────

    public function setRound(array $data): void
    {
        Cache::put(self::ROUND_KEY, $data, self::TTL);
    }

    public function getRound(): ?array
    {
        return Cache::get(self::ROUND_KEY);
    }

    public function updateRoundField(string $field, mixed $value): void
    {
        $round         = $this->getRound() ?? [];
        $round[$field] = $value;
        $this->setRound($round);
    }

    public function clearRound(): void
    {
        Cache::forget(self::ROUND_KEY);
    }

    // ── Timer ─────────────────────────────────────────────────────────────────

    public function setTimerEndsAt(int $unixTimestamp): void
    {
        Cache::put(self::TIMER_KEY, $unixTimestamp, self::TTL);
    }

    /**
     * Seconds remaining in the betting phase.
     * Returns 0 when no timer is set — GameController recalculates from DB started_at.
     */
    public function getTimerRemaining(): int
    {
        $endsAt = Cache::get(self::TIMER_KEY);
        if (! $endsAt) return 0;
        return max(0, (int) $endsAt - time());
    }

    public function clearTimer(): void
    {
        Cache::forget(self::TIMER_KEY);
    }

    // ── Active players ────────────────────────────────────────────────────────

    public function addActivePlayer(int $playerId, string $playerName): void
    {
        $players = Cache::get(self::PLAYERS_KEY, []);
        $players[$playerId] = $playerName;
        Cache::put(self::PLAYERS_KEY, $players, self::TTL);
    }

    public function removeActivePlayer(int $playerId): void
    {
        $players = Cache::get(self::PLAYERS_KEY, []);
        unset($players[$playerId]);
        Cache::put(self::PLAYERS_KEY, $players, self::TTL);
    }

    /**
     * Returns {playerId: playerName} or empty array.
     */
    public function getActivePlayers(): array
    {
        return Cache::get(self::PLAYERS_KEY, []);
    }

    public function clearActivePlayers(): void
    {
        Cache::forget(self::PLAYERS_KEY);
    }

    // ── Full reset ────────────────────────────────────────────────────────────

    public function flushGameState(): void
    {
        Cache::forget(self::ROUND_KEY);
        Cache::forget(self::TIMER_KEY);
        Cache::forget(self::PLAYERS_KEY);
    }
}
