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
 * Run this once when the server boots.
 * After the first round, ProcessRoundTimer auto-creates the next one.
 */
class StartGame extends Command
{
    protected $signature   = 'game:start {--force : Reset all state and start fresh}';
    protected $description = 'Bootstrap the Lucky Puffin game loop';

    public function handle(GameEngineService $engine, RedisStateService $redis): int
    {
        $this->info('🐧 Lucky Puffin — Starting game loop...');

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

        // Check for any stuck 'dealing' rounds and clean them up
        $stuckRounds = GameRound::where('round_status', RoundStatus::Dealing->value)
            ->whereNull('player_cards')
            ->get();

        if ($stuckRounds->isNotEmpty()) {
            $this->warn("Found {$stuckRounds->count()} stuck round(s) — resetting to waiting");
            $stuckRounds->each(fn ($r) => $r->update([
                'round_status' => RoundStatus::Waiting,
                'started_at'   => null,
                'dealing_ends_at' => null,
            ]));
        }

        // Create the first waiting round
        $round = $engine->createWaitingRound();

        $this->info("✅ Waiting round #{$round->id} created");
        $this->info('');
        $this->info('Game is ready. Players can now join and bet.');
        $this->info('The timer starts automatically when the first bet is placed.');
        $this->info('');
        $this->warn('Make sure the queue worker is running:');
        $this->line('  php artisan queue:work database --queue=game,default');
        $this->warn('Make sure Reverb is running:');
        $this->line('  php artisan reverb:start --port=8080');

        return self::SUCCESS;
    }
}
