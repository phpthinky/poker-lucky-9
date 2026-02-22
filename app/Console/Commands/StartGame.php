<?php

namespace App\Console\Commands;

use App\Models\GameRound;
use App\Models\RoundStatus;
use App\Services\GameEngineService;
use App\Services\RedisStateService;
use Illuminate\Console\Command;

/**
 * Usage:
 *   php artisan game:start          → Start if no active round
 *   php artisan game:start --force  → Force reset and start fresh
 *
 * No queue worker or cron needed — dealing is triggered by client requests.
 */
class StartGame extends Command
{
    protected $signature   = 'game:start {--force : Reset all state and start fresh}';
    protected $description = 'Bootstrap the Lucky Puffin game loop';

    public function handle(GameEngineService $engine, RedisStateService $redis): int
    {
        $this->info('Lucky Puffin — Starting game loop...');

        if ($this->option('force')) {
            $this->warn('Force flag set — resetting all state');
            $redis->flushGameState();
        }

        // Check for existing active round
        $activeRound = GameRound::active()->latest()->first();

        if ($activeRound && ! $this->option('force')) {
            $this->info("Active round found: #{$activeRound->id} ({$activeRound->round_status->value})");
            $this->info('Game loop is already running. Use --force to reset.');
            return self::SUCCESS;
        }

        // Reset stuck 'dealing' rounds (no cards — something crashed mid-deal)
        $stuckDealing = GameRound::where('round_status', RoundStatus::Dealing->value)
            ->whereNull('player_cards')
            ->get();

        if ($stuckDealing->isNotEmpty()) {
            $this->warn("Found {$stuckDealing->count()} stuck dealing round(s) — resetting to waiting");
            $stuckDealing->each(fn ($r) => $r->update([
                'round_status'   => RoundStatus::Waiting,
                'started_at'     => null,
                'round_ends_at'  => null,
                'dealing_ends_at' => null,
            ]));
        }

        // Reset stuck 'betting' rounds where round_ends_at was more than 5 min ago
        $stuckBetting = GameRound::where('round_status', RoundStatus::Betting->value)
            ->where('round_ends_at', '<=', now()->subMinutes(5))
            ->get();

        if ($stuckBetting->isNotEmpty()) {
            $this->warn("Found {$stuckBetting->count()} stuck betting round(s) — resetting to waiting");
            $stuckBetting->each(fn ($r) => $r->update([
                'round_status'  => RoundStatus::Waiting,
                'started_at'    => null,
                'round_ends_at' => null,
            ]));
        }

        // Create the first waiting round
        $round = $engine->createWaitingRound();

        $this->info("Waiting round #{$round->id} created");
        $this->info('');
        $this->info('Game is ready. Players can now join and bet.');
        $this->info('Dealing triggers automatically when the first client polls after the betting window closes.');
        $this->info('No queue worker or cron required.');

        return self::SUCCESS;
    }
}
