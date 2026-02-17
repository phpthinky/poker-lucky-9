<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Activity feed — recent wins/losses shown to all players
        Schema::create('activity_feed', function (Blueprint $table) {
            $table->id();

            $table->foreignId('round_id')
                  ->nullable()
                  ->constrained('game_rounds')
                  ->nullOnDelete();

            $table->foreignId('player_id')
                  ->constrained('players')
                  ->cascadeOnDelete();

            $table->string('player_name', 50);

            $table->enum('activity_type', [
                'placed_bet',
                'won',
                'big_win',
                'lost',
                'returned',   // Tie - bet returned
                'bonus',
            ]);

            $table->integer('amount')->default(0);
            $table->string('message', 200)->nullable();

            $table->timestamp('created_at')->useCurrent();

            // Only keep recent rows — index for fast DESC query
            $table->index(['created_at']);
        });

        // Bonus claims — track hourly bonus per player
        Schema::create('bonus_claims', function (Blueprint $table) {
            $table->id();

            $table->foreignId('player_id')
                  ->constrained('players')
                  ->cascadeOnDelete();

            $table->integer('bonus_amount');

            $table->timestamp('claimed_at')->useCurrent();

            $table->index(['player_id', 'claimed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_feed');
        Schema::dropIfExists('bonus_claims');
    }
};
