<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('round_bets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('round_id')
                  ->constrained('game_rounds')
                  ->cascadeOnDelete();

            $table->foreignId('player_id')
                  ->constrained('players')
                  ->cascadeOnDelete();

            $table->string('player_name', 50);              // Denormalized for speed

            // Individual bet amounts
            $table->integer('bet_player')->default(0);
            $table->integer('bet_banker')->default(0);
            $table->integer('bet_tie')->default(0);
            $table->integer('bet_player_pair')->default(0);
            $table->integer('bet_banker_pair')->default(0);
            $table->integer('bet_random_pair')->default(0); // Module 7

            $table->integer('total_bet')->default(0);
            $table->integer('total_won')->default(0);

            $table->timestamps();

            // One bet record per player per round (updated on each chip click)
            $table->unique(['round_id', 'player_id']);
            $table->index('player_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('round_bets');
    }
};
