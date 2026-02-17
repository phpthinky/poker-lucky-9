<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_rounds', function (Blueprint $table) {
            $table->id();

            $table->enum('round_status', [
                'waiting',
                'betting',
                'dealing',
                'finished',
            ])->default('waiting');

            // Cards stored as JSON arrays
            $table->json('player_cards')->nullable();
            $table->json('banker_cards')->nullable();

            $table->tinyInteger('player_total')->default(0);
            $table->tinyInteger('banker_total')->default(0);

            $table->enum('result', [
                'PLAYER_WINS',
                'BANKER_WINS',
                'TIE',
            ])->nullable();

            // Pair flags
            $table->boolean('is_player_pair')->default(false);
            $table->boolean('is_banker_pair')->default(false);
            $table->boolean('is_random_pair')->default(false);  // Module 7

            // Timestamps for each phase
            $table->timestamp('started_at')->nullable();        // When betting began
            $table->timestamp('dealing_ends_at')->nullable();   // When dealing overlay closes
            $table->timestamp('finished_at')->nullable();       // When result phase begins

            $table->timestamps();

            // Indexes
            $table->index('round_status');
            $table->index(['round_status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_rounds');
    }
};
