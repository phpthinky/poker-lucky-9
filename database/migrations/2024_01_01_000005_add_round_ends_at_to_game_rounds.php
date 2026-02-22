<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds round_ends_at: the authoritative UTC timestamp when betting closes.
 *
 * This replaces the queue-job approach (ProcessRoundTimer) with a pure
 * time-based state machine:
 *   - Set once when first bet is placed: round_ends_at = now() + betting_duration
 *   - The server checks now() >= round_ends_at on every state request
 *   - Transition is atomic: UPDATE … WHERE round_status='betting' AND round_ends_at <= now()
 *   - The frontend calculates the countdown as (round_ends_at - Date.now()) — no server ticks
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_rounds', function (Blueprint $table) {
            $table->timestamp('round_ends_at')
                  ->nullable()
                  ->after('started_at')
                  ->comment('UTC timestamp when the betting phase closes');
        });
    }

    public function down(): void
    {
        Schema::table('game_rounds', function (Blueprint $table) {
            $table->dropColumn('round_ends_at');
        });
    }
};
