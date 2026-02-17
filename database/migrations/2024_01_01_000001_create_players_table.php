<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->string('guest_id', 100)->unique();
            $table->string('player_name', 50);

            // Future auth (Module 9)
            $table->string('email')->nullable()->unique();
            $table->string('password')->nullable();
            $table->string('avatar')->nullable();
            $table->string('fcm_token')->nullable();        // Firebase push (Module 9)

            $table->integer('balance')->default(1000);
            $table->integer('total_games_played')->default(0);
            $table->integer('total_winnings')->default(0);

            $table->timestamp('last_visit')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('guest_id');
            $table->index(['balance', 'id']);               // Leaderboard ORDER BY balance DESC
            $table->index('last_visit');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
