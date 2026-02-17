<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;

class RedisStateService
{
    private const ROUND_KEY   = 'lucky_puffin:round';
    private const TIMER_KEY   = 'lucky_puffin:timer_ends_at';
    private const PLAYERS_KEY = 'lucky_puffin:active_players';
    private const TTL         = 3600; // 1 hour

    // ─── ROUND STATE ────────────────────────────────────────────

    public function setRound(array $data): void
    {
        Redis::setex(self::ROUND_KEY, self::TTL, json_encode($data));
    }

    public function getRound(): ?array
    {
        $raw = Redis::get(self::ROUND_KEY);
        return $raw ? json_decode($raw, true) : null;
    }

    public function updateRoundField(string $field, mixed $value): void
    {
        $round = $this->getRound() ?? [];
        $round[$field] = $value;
        $this->setRound($round);
    }

    public function clearRound(): void
    {
        Redis::del(self::ROUND_KEY);
    }

    // ─── TIMER ──────────────────────────────────────────────────

    /**
     * Store Unix timestamp when betting phase ends.
     */
    public function setTimerEndsAt(int $unixTimestamp): void
    {
        Redis::setex(self::TIMER_KEY, self::TTL, $unixTimestamp);
    }

    /**
     * Seconds remaining in betting phase.
     */
    public function getTimerRemaining(): int
    {
        $endsAt = Redis::get(self::TIMER_KEY);
        if (! $endsAt) {
            return 0;
        }
        return max(0, (int) $endsAt - time());
    }

    public function clearTimer(): void
    {
        Redis::del(self::TIMER_KEY);
    }

    // ─── ACTIVE PLAYERS ─────────────────────────────────────────

    /**
     * Track which players have placed bets in current round.
     * Stored as hash: { player_id => player_name }
     */
    public function addActivePlayer(int $playerId, string $playerName): void
    {
        Redis::hset(self::PLAYERS_KEY, $playerId, $playerName);
        Redis::expire(self::PLAYERS_KEY, self::TTL);
    }

    public function removeActivePlayer(int $playerId): void
    {
        Redis::hdel(self::PLAYERS_KEY, $playerId);
    }

    public function getActivePlayers(): array
    {
        return Redis::hgetall(self::PLAYERS_KEY) ?? [];
    }

    public function clearActivePlayers(): void
    {
        Redis::del(self::PLAYERS_KEY);
    }

    // ─── FULL RESET ─────────────────────────────────────────────

    /**
     * Clear all Lucky Puffin Redis keys (use at round start).
     */
    public function flushGameState(): void
    {
        Redis::del([self::ROUND_KEY, self::TIMER_KEY, self::PLAYERS_KEY]);
    }
}
