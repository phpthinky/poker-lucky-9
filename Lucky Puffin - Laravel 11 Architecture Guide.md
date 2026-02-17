# 🐧 Lucky Puffin — Laravel 11 Architecture Guide
## Full Migration + Future Roadmap

---

# TABLE OF CONTENTS

1. [Tech Stack Overview](#tech-stack)
2. [Folder Structure](#folder-structure)
3. [Module 1 — Database & Migrations](#module-1)
4. [Module 2 — Redis Shared State](#module-2)
5. [Module 3 — WebSocket Server (Ratchet + Laravel Echo)](#module-3)
6. [Module 4 — Game Engine (Round Lifecycle)](#module-4)
7. [Module 5 — REST API (Fallback + Auth)](#module-5)
8. [Module 6 — Frontend (Bootstrap + bootstrap ui-Mix/bootstrap 5.3)](#module-6)
9. [Module 7 — Future: Random Pair Bonus](#module-7)
10. [Module 8 — Future: Community Thread](#module-8)
11. [Module 9 — Future: Mobile App Integration](#module-9)
12. [Deployment Guide](#deployment)
13. [Migration Checklist](#checklist)

---

# 1. TECH STACK OVERVIEW {#tech-stack}

```
┌─────────────────────────────────────────────┐
│                 CLIENT LAYER                │
│  Browser / Mobile App                       │
│  - Bootstrap 5 (Vite Mix)                  │
│  - Laravel Echo (WebSocket client)          │
│  - Axios (REST fallback)                    │
└──────────────────┬──────────────────────────┘
                   │ WS + HTTP
┌──────────────────▼──────────────────────────┐
│              APPLICATION LAYER              │
│  Laravel 11 / PHP 8.2                       │
│  - Routes: web.php / api.php / channels.php │
│  - Controllers: Game, Auth, Community       │
│  - Events: RoundStarted, CardDealt, etc.    │
│  - Jobs: ProcessRound (queued)              │
└──────────┬───────────────┬──────────────────┘
           │               │
┌──────────▼────┐  ┌───────▼───────────────────┐
│  REDIS LAYER  │  │     DATABASE LAYER         │
│  - Round state│  │  MySQL / MariaDB           │
│  - Timer sync │  │  - players                 │
│  - Pub/Sub    │  │  - game_rounds             │
│  - Sessions   │  │  - round_bets              │
└───────────────┘  │  - community_posts         │
                   │  - pair_bonuses            │
                   └────────────────────────────┘
```

## Why Each Technology

| Tech | Why |
|------|-----|
| **Laravel 11** | Clean routing, Eloquent ORM, Events, Queues, Echo |
| **PHP 8.2** | Fibers, readonly classes, enums for game states |
| **WebSocket (Ratchet)** | Real-time push (no polling!), all players in sync |
| **Laravel Echo** | Clean JS WebSocket client, auto-reconnects |
| **Redis** | Round state in memory (~0ms read vs ~5ms DB), Pub/Sub for WS |
| **Vite Mix** | Fast asset bundling, Bootstrap + JS compilation |

### Current vs New System

| Feature | Current (Polling) | New (WebSocket) |
|---------|------------------|-----------------|
| Round sync | Every 500ms poll | Instant push |
| Timer | Server recalculates each poll | Server pushes tick |
| Cards deal | Detected on next poll | Instant broadcast |
| DB load | 120+ queries/minute | ~5 queries/minute |
| Scale | ~10 players | ~1000+ players |

---

# 2. FOLDER STRUCTURE {#folder-structure}

```
lucky-puffin/
├── app/
│   ├── Console/Commands/
│   │   └── StartGameServer.php        ← Ratchet WebSocket server
│   │
│   ├── Events/                        ← Laravel Events (broadcast via WS)
│   │   ├── RoundStarted.php
│   │   ├── BetPlaced.php
│   │   ├── CardsDealt.php
│   │   ├── RoundFinished.php
│   │   └── TimerTick.php
│   │
│   ├── Http/Controllers/
│   │   ├── GameController.php         ← REST API: bet, get state
│   │   ├── AuthController.php         ← Guest/player auth
│   │   ├── CommunityController.php    ← Thread/posts (Module 8)
│   │   └── LeaderboardController.php
│   │
│   ├── Jobs/
│   │   └── ProcessRoundTimer.php      ← Redis-driven timer job
│   │
│   ├── Models/
│   │   ├── Player.php
│   │   ├── GameRound.php
│   │   ├── RoundBet.php
│   │   ├── CommunityPost.php          ← Module 8
│   │   └── PairBonus.php              ← Module 7
│   │
│   ├── Services/
│   │   ├── GameEngineService.php      ← Core game logic
│   │   ├── RedisStateService.php      ← Redis read/write
│   │   ├── DeckService.php            ← Card dealing
│   │   ├── PayoutService.php          ← Payout calculation
│   │   └── BonusService.php           ← Pair bonus (Module 7)
│   │
│   └── WebSocket/
│       ├── GameServer.php             ← Ratchet server entry
│       └── MessageHandler.php         ← Incoming WS messages
│
├── channels.php                       ← Broadcast channel auth
├── routes/
│   ├── web.php                        ← Game page routes
│   ├── api.php                        ← API routes (mobile too)
│   └── channels.php                   ← Echo channel definitions
│
├── resources/
│   ├── js/
│   │   ├── app.js                     ← Bootstrap + Echo init
│   │   ├── game.js                    ← Main game logic
│   │   ├── betting.js                 ← Bet handling
│   │   ├── animation.js               ← Card dealing animation
│   │   └── community.js               ← Thread/chat (Module 8)
│   └── views/
│       ├── game/index.blade.php
│       └── community/index.blade.php
│
├── config/
│   ├── game.php                       ← Game config (timers, payouts)
│   └── websockets.php
│
└── docker-compose.yml                 ← Redis + MySQL setup
```

---

# 3. MODULE 1 — DATABASE & MIGRATIONS {#module-1}

## Config: `config/game.php`

```php
<?php
return [
    'betting_duration'   => 20,   // seconds
    'dealing_duration'   => 5,    // seconds cards shown
    'result_duration'    => 5,    // seconds result shown
    'hourly_bonus'       => 10,   // WPUFF per hour
    'max_bonus_hours'    => 5,    // max 50 WPUFF
    'starting_balance'   => 1000,

    'payouts' => [
        'player'      => 2,   // 1:1 (return stake + winnings)
        'banker'      => 2,   // 1:1
        'tie'         => 8,   // 7:1
        'player_pair' => 11,  // 10:1
        'banker_pair' => 11,  // 10:1
        'random_pair' => 5,   // Module 7 — 5x bonus
    ],
];
```

## Migration: Players

```php
// database/migrations/create_players_table.php
Schema::create('players', function (Blueprint $table) {
    $table->id();
    $table->string('guest_id')->unique();
    $table->string('player_name', 50);
    $table->string('email')->nullable()->unique();   // For future auth
    $table->string('password')->nullable();          // For future auth
    $table->string('avatar')->nullable();            // Module 9
    $table->string('fcm_token')->nullable();         // Module 9 (push notif)
    $table->integer('balance')->default(1000);
    $table->integer('total_games_played')->default(0);
    $table->integer('total_winnings')->default(0);
    $table->timestamp('last_visit')->nullable();
    $table->timestamps();

    $table->index('guest_id');
    $table->index(['balance', 'id']);     // Leaderboard query
});
```

## Migration: Game Rounds

```php
// database/migrations/create_game_rounds_table.php
Schema::create('game_rounds', function (Blueprint $table) {
    $table->id();
    $table->enum('round_status', [
        'waiting', 'betting', 'dealing', 'finished'
    ])->default('waiting');

    $table->json('player_cards')->nullable();
    $table->json('banker_cards')->nullable();
    $table->tinyInteger('player_total')->default(0);
    $table->tinyInteger('banker_total')->default(0);

    $table->enum('result', [
        'PLAYER_WINS', 'BANKER_WINS', 'TIE'
    ])->nullable();

    $table->boolean('is_player_pair')->default(false);
    $table->boolean('is_banker_pair')->default(false);
    $table->boolean('is_random_pair')->default(false);  // Module 7

    $table->timestamp('started_at')->nullable();
    $table->timestamp('dealing_ends_at')->nullable();
    $table->timestamp('finished_at')->nullable();
    $table->timestamps();

    $table->index('round_status');
    $table->index('created_at');
});
```

## Migration: Round Bets

```php
Schema::create('round_bets', function (Blueprint $table) {
    $table->id();
    $table->foreignId('round_id')->constrained('game_rounds')->cascadeOnDelete();
    $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
    $table->string('player_name', 50);

    $table->integer('bet_player')->default(0);
    $table->integer('bet_banker')->default(0);
    $table->integer('bet_tie')->default(0);
    $table->integer('bet_player_pair')->default(0);
    $table->integer('bet_banker_pair')->default(0);
    $table->integer('bet_random_pair')->default(0);   // Module 7
    $table->integer('total_bet')->default(0);
    $table->integer('total_won')->default(0);

    $table->timestamps();

    $table->unique(['round_id', 'player_id']);
    $table->index('player_id');
});
```

## Model: GameRound with Enum State

```php
<?php
// app/Models/GameRound.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

enum RoundStatus: string {
    case Waiting  = 'waiting';
    case Betting  = 'betting';
    case Dealing  = 'dealing';
    case Finished = 'finished';
}

class GameRound extends Model {
    protected $fillable = [
        'round_status', 'player_cards', 'banker_cards',
        'player_total', 'banker_total', 'result',
        'is_player_pair', 'is_banker_pair', 'is_random_pair',
        'started_at', 'dealing_ends_at', 'finished_at',
    ];

    protected $casts = [
        'player_cards'   => 'array',
        'banker_cards'   => 'array',
        'is_player_pair' => 'boolean',
        'is_banker_pair' => 'boolean',
        'is_random_pair' => 'boolean',
        'started_at'     => 'datetime',
        'dealing_ends_at'=> 'datetime',
        'finished_at'    => 'datetime',
        'round_status'   => RoundStatus::class,
    ];

    public function bets() {
        return $this->hasMany(RoundBet::class, 'round_id');
    }

    public function getTimerRemainingAttribute(): int {
        if ($this->round_status !== RoundStatus::Betting || !$this->started_at) {
            return 0;
        }
        return max(0, config('game.betting_duration') - now()->diffInSeconds($this->started_at));
    }
}
```

---

# 4. MODULE 2 — REDIS SHARED STATE {#module-2}

## Why Redis?

```
Without Redis:           With Redis:
DB query every 500ms     Memory read ~0.1ms
~120 queries/min         ~2 queries/min (only on state change)
Gets slow with players   Scales to 1000+ players
```

## RedisStateService

```php
<?php
// app/Services/RedisStateService.php

namespace App\Services;

use Illuminate\Support\Facades\Redis;

class RedisStateService
{
    private const ROUND_KEY    = 'lucky_puffin:round';
    private const TIMER_KEY    = 'lucky_puffin:timer';
    private const PLAYERS_KEY  = 'lucky_puffin:players';
    private const TTL          = 3600; // 1 hour

    // Store full round state in Redis
    public function setRound(array $roundData): void {
        Redis::setex(self::ROUND_KEY, self::TTL, json_encode($roundData));
    }

    // Get round state from memory
    public function getRound(): ?array {
        $data = Redis::get(self::ROUND_KEY);
        return $data ? json_decode($data, true) : null;
    }

    // Update only specific fields (no full rewrite)
    public function updateRoundField(string $field, mixed $value): void {
        $round = $this->getRound() ?? [];
        $round[$field] = $value;
        $this->setRound($round);
    }

    // Timer: stores server-side countdown end time
    public function setTimerEndsAt(int $timestamp): void {
        Redis::setex(self::TIMER_KEY, self::TTL, $timestamp);
    }

    public function getTimerRemaining(): int {
        $endsAt = Redis::get(self::TIMER_KEY);
        if (!$endsAt) return 0;
        return max(0, $endsAt - time());
    }

    // Track active players (connected via WS)
    public function addActivePlayer(int $playerId, string $name): void {
        Redis::hset(self::PLAYERS_KEY, $playerId, $name);
        Redis::expire(self::PLAYERS_KEY, self::TTL);
    }

    public function removeActivePlayer(int $playerId): void {
        Redis::hdel(self::PLAYERS_KEY, $playerId);
    }

    public function getActivePlayers(): array {
        return Redis::hgetall(self::PLAYERS_KEY) ?? [];
    }

    // Pub/Sub: Publish event to all subscribers
    public function publish(string $event, array $data): void {
        Redis::publish('lucky_puffin:events', json_encode([
            'event' => $event,
            'data'  => $data,
        ]));
    }

    // Clear all state (fresh start)
    public function flush(): void {
        Redis::del([self::ROUND_KEY, self::TIMER_KEY, self::PLAYERS_KEY]);
    }
}
```

---

# 5. MODULE 3 — WEBSOCKET SERVER {#module-3}

## Two Options

### Option A: Ratchet (Pure PHP, self-hosted)
**Best for:** VPS control, low latency, no Node.js
```
Browser ←→ Ratchet (PHP) ←→ Redis Pub/Sub ←→ Laravel
```

### Option B: Laravel Reverb (Official, Laravel 11+)
**Best for:** Easiest setup, Laravel native integration
```
Browser ←→ Laravel Reverb (built-in) ←→ Events
```

### Recommendation: **Laravel Reverb** (install in 1 command)

```bash
composer require laravel/reverb
php artisan reverb:install
```

## Install All WebSocket Dependencies

```bash
# Backend
composer require laravel/reverb
composer require predis/predis   # Redis client

# Frontend
npm install laravel-echo pusher-js

# Queue worker for jobs
composer require laravel/horizon   # Optional but recommended
```

## .env Configuration

```env
# WebSocket
BROADCAST_DRIVER=reverb
REVERB_APP_ID=lucky-puffin
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

# Redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

## Events (Broadcast to All Players)

```php
<?php
// app/Events/RoundStarted.php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class RoundStarted implements ShouldBroadcast
{
    public function __construct(
        public readonly int    $roundId,
        public readonly int    $timerSeconds,
        public readonly string $status,
    ) {}

    public function broadcastOn(): Channel {
        return new Channel('game-table');  // Public channel, all players
    }

    public function broadcastAs(): string {
        return 'round.started';
    }
}
```

```php
<?php
// app/Events/CardsDealt.php
class CardsDealt implements ShouldBroadcast {
    public function __construct(
        public readonly int    $roundId,
        public readonly array  $playerCards,
        public readonly array  $bankerCards,
        public readonly int    $playerTotal,
        public readonly int    $bankerTotal,
        public readonly string $result,
        public readonly bool   $isPlayerPair,
        public readonly bool   $isBankerPair,
        public readonly bool   $isRandomPair,  // Module 7
    ) {}

    public function broadcastOn(): Channel {
        return new Channel('game-table');
    }

    public function broadcastAs(): string {
        return 'cards.dealt';
    }
}
```

```php
<?php
// app/Events/TimerTick.php
// Broadcasts every second during betting phase
class TimerTick implements ShouldBroadcast {
    public function __construct(
        public readonly int $roundId,
        public readonly int $secondsRemaining,
    ) {}

    public function broadcastOn(): Channel {
        return new Channel('game-table');
    }

    public function broadcastAs(): string {
        return 'timer.tick';
    }
}
```

```php
<?php
// app/Events/BetPlaced.php
// Broadcasts to ALL so they see activity feed
class BetPlaced implements ShouldBroadcast {
    public function __construct(
        public readonly string $playerName,
        public readonly int    $totalBet,
        public readonly int    $activePlayers,
    ) {}

    public function broadcastOn(): Channel {
        return new Channel('game-table');
    }

    public function broadcastAs(): string {
        return 'bet.placed';
    }
}
```

```php
<?php
// app/Events/PlayerResult.php
// Private channel - only the player who bet sees their result
use Illuminate\Broadcasting\PrivateChannel;

class PlayerResult implements ShouldBroadcast {
    public function __construct(
        public readonly int    $playerId,
        public readonly string $result,
        public readonly int    $totalBet,
        public readonly int    $totalWon,
        public readonly int    $profit,
        public readonly int    $newBalance,
    ) {}

    public function broadcastOn(): PrivateChannel {
        return new PrivateChannel("player.{$this->playerId}");
    }

    public function broadcastAs(): string {
        return 'round.result';
    }
}
```

## channels.php (Channel Authorization)

```php
<?php
// routes/channels.php

// Private player channel (only that player)
Broadcast::channel('player.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Game table is public - anyone can join
// (handled by public Channel, no auth needed)
```

## Timer Job (Runs on Queue Worker)

```php
<?php
// app/Jobs/ProcessRoundTimer.php

namespace App\Jobs;

use App\Events\TimerTick;
use App\Services\GameEngineService;
use App\Services\RedisStateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProcessRoundTimer implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly int $roundId,
    ) {}

    public function handle(
        GameEngineService  $engine,
        RedisStateService  $redis,
    ): void {
        $secondsRemaining = config('game.betting_duration');

        while ($secondsRemaining >= 0) {
            // Broadcast timer tick to all players
            broadcast(new TimerTick($this->roundId, $secondsRemaining));

            if ($secondsRemaining === 0) {
                // Deal cards when timer hits 0
                $engine->dealCards($this->roundId);
                break;
            }

            $secondsRemaining--;
            sleep(1); // Real 1-second intervals
        }
    }
}
```

## Frontend: Laravel Echo Setup (resources/js/app.js)

```javascript
// resources/js/app.js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',   // or 'pusher'
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    wssPort: import.meta.env.VITE_REVERB_PORT,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
});
```

## Frontend: Event Listeners (resources/js/game.js)

```javascript
// resources/js/game.js

// Listen to public game table channel
window.Echo.channel('game-table')

    .listen('.round.started', (data) => {
        console.log('Round started:', data.roundId);
        currentRoundId = data.roundId;
        clearBets();          // Auto-clear from previous round
        enableBetting();
        startLocalTimer(data.timerSeconds);
    })

    .listen('.timer.tick', (data) => {
        // No polling needed! Server pushes every second
        updateTimerDisplay(data.secondsRemaining);
    })

    .listen('.bet.placed', (data) => {
        // Another player bet - update activity feed
        updateActivityFeed({
            message: `${data.playerName} bet ${data.totalBet} WPUFF`,
            type: 'placed_bet',
        });
        updatePlayerCount(data.activePlayers);
    })

    .listen('.cards.dealt', (data) => {
        // ALL players see cards at exact same time
        disableBetting();
        showSharedCards(data);      // Your card animation function
    });

// Private channel for this player's result only
window.Echo.private(`player.${playerId}`)

    .listen('.round.result', (data) => {
        // Only this player sees their win/loss
        showResult(data);
        syncBalance(data.newBalance);
    });
```

---

# 6. MODULE 4 — GAME ENGINE SERVICE {#module-4}

```php
<?php
// app/Services/GameEngineService.php

namespace App\Services;

use App\Events\{BetPlaced, CardsDealt, PlayerResult, RoundFinished, RoundStarted};
use App\Models\{GameRound, Player, RoundBet};
use Illuminate\Support\Facades\DB;

class GameEngineService
{
    public function __construct(
        private RedisStateService $redis,
        private DeckService       $deck,
        private PayoutService     $payouts,
        private BonusService      $bonus,    // Module 7
    ) {}

    // ─── ROUND LIFECYCLE ───────────────────────────

    public function createWaitingRound(): GameRound {
        $round = GameRound::create(['round_status' => 'waiting']);
        $this->redis->setRound($round->toArray());
        return $round;
    }

    public function startBetting(int $roundId): void {
        $round = GameRound::findOrFail($roundId);
        $round->update([
            'round_status' => 'betting',
            'started_at'   => now(),
        ]);

        $endsAt = now()->addSeconds(config('game.betting_duration'))->timestamp;
        $this->redis->setTimerEndsAt($endsAt);
        $this->redis->updateRoundField('round_status', 'betting');

        broadcast(new RoundStarted(
            roundId:      $roundId,
            timerSeconds: config('game.betting_duration'),
            status:       'betting',
        ));
    }

    public function placeBet(int $roundId, int $playerId, array $bets): RoundBet {
        $totalBet = array_sum($bets);
        $player   = Player::findOrFail($playerId);

        DB::transaction(function () use ($roundId, $playerId, $bets, $totalBet, $player, &$bet) {
            // Upsert bet (handles repeated clicks)
            $bet = RoundBet::updateOrCreate(
                ['round_id' => $roundId, 'player_id' => $playerId],
                array_merge($bets, ['total_bet' => $totalBet]),
            );

            // Track in Redis
            $this->redis->addActivePlayer($playerId, $player->player_name);
        });

        // Broadcast to all players
        $activePlayers = count($this->redis->getActivePlayers());
        broadcast(new BetPlaced(
            playerName:    $player->player_name,
            totalBet:      $totalBet,
            activePlayers: $activePlayers,
        ))->toOthers();   // Exclude sender (they already know)

        return $bet;
    }

    public function dealCards(int $roundId): void {
        $round = GameRound::findOrFail($roundId);

        // Already dealt
        if ($round->player_cards) return;

        [$playerCards, $bankerCards] = $this->deck->deal();

        $playerTotal   = $this->deck->calculateTotal($playerCards);
        $bankerTotal   = $this->deck->calculateTotal($bankerCards);
        $result        = $this->determineWinner($playerTotal, $bankerTotal);
        $isPlayerPair  = $this->deck->isPair($playerCards);
        $isBankerPair  = $this->deck->isPair($bankerCards);
        $isRandomPair  = $this->bonus->checkRandomPair($playerCards, $bankerCards); // Module 7

        $round->update([
            'round_status'    => 'dealing',
            'player_cards'    => $playerCards,
            'banker_cards'    => $bankerCards,
            'player_total'    => $playerTotal,
            'banker_total'    => $bankerTotal,
            'result'          => $result,
            'is_player_pair'  => $isPlayerPair,
            'is_banker_pair'  => $isBankerPair,
            'is_random_pair'  => $isRandomPair,
            'dealing_ends_at' => now()->addSeconds(config('game.dealing_duration')),
        ]);

        $this->redis->updateRoundField('round_status', 'dealing');
        $this->redis->updateRoundField('player_cards', $playerCards);
        $this->redis->updateRoundField('banker_cards', $bankerCards);

        // Broadcast cards to ALL players at once
        broadcast(new CardsDealt(
            roundId:      $roundId,
            playerCards:  $playerCards,
            bankerCards:  $bankerCards,
            playerTotal:  $playerTotal,
            bankerTotal:  $bankerTotal,
            result:       $result,
            isPlayerPair: $isPlayerPair,
            isBankerPair: $isBankerPair,
            isRandomPair: $isRandomPair,
        ));

        // Calculate payouts
        $this->calculatePayouts($round);
    }

    private function calculatePayouts(GameRound $round): void {
        $bets = $round->bets()->with('player')->get();

        foreach ($bets as $bet) {
            $winnings = $this->payouts->calculate($bet, $round);
            $netChange = $winnings - $bet->total_bet;

            DB::transaction(function () use ($bet, $winnings, $netChange) {
                $bet->update(['total_won' => $winnings]);
                $bet->player->increment('balance', $netChange);
                $bet->player->increment('total_games_played');
                $bet->player->increment('total_winnings', $netChange);
            });

            // Send private result to each player
            broadcast(new PlayerResult(
                playerId:   $bet->player_id,
                result:     $round->result->value,
                totalBet:   $bet->total_bet,
                totalWon:   $winnings,
                profit:     $netChange,
                newBalance: $bet->player->fresh()->balance,
            ));
        }
    }

    private function determineWinner(int $playerTotal, int $bankerTotal): string {
        return match(true) {
            $playerTotal > $bankerTotal => 'PLAYER_WINS',
            $bankerTotal > $playerTotal => 'BANKER_WINS',
            default                     => 'TIE',
        };
    }
}
```

## DeckService

```php
<?php
// app/Services/DeckService.php

namespace App\Services;

class DeckService
{
    private const SUITS  = ['♥', '♦', '♣', '♠'];
    private const VALUES = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];

    public function deal(): array {
        $deck = $this->createDeck();
        shuffle($deck);

        $playerCards = [array_pop($deck), array_pop($deck)];
        $bankerCards = [array_pop($deck), array_pop($deck)];

        // Third card rules
        if ($this->calculateTotal($playerCards) <= 5) {
            $playerCards[] = array_pop($deck);
        }
        if ($this->calculateTotal($bankerCards) <= 5) {
            $bankerCards[] = array_pop($deck);
        }

        return [$playerCards, $bankerCards];
    }

    public function calculateTotal(array $cards): int {
        $total = array_reduce($cards, function ($carry, $card) {
            return $carry + match($card['value']) {
                'A'             => 1,
                'J', 'Q', 'K', '10' => 0,
                default         => (int) $card['value'],
            };
        }, 0);

        return $total % 10;
    }

    public function isPair(array $cards): bool {
        return count($cards) >= 2 && $cards[0]['value'] === $cards[1]['value'];
    }

    private function createDeck(): array {
        $deck = [];
        foreach (self::SUITS as $suit) {
            foreach (self::VALUES as $value) {
                $deck[] = ['suit' => $suit, 'value' => $value, 'display' => $value . $suit];
            }
        }
        return $deck;
    }
}
```

---

# 7. MODULE 5 — REST API {#module-5}

```php
<?php
// routes/api.php

Route::prefix('v1')->group(function () {

    // Auth
    Route::post('/guest/login', [AuthController::class, 'guestLogin']);
    Route::post('/register',   [AuthController::class, 'register']);
    Route::post('/login',      [AuthController::class, 'login']);

    // Protected routes (Sanctum token)
    Route::middleware('auth:sanctum')->group(function () {

        // Game
        Route::get('/game/state',   [GameController::class, 'state']);
        Route::post('/game/bet',    [GameController::class, 'placeBet']);
        Route::post('/game/clear',  [GameController::class, 'clearBet']);

        // Player
        Route::get('/player',       [GameController::class, 'profile']);
        Route::get('/leaderboard',  [LeaderboardController::class, 'index']);

        // Community (Module 8)
        Route::apiResource('posts', CommunityController::class);
        Route::post('posts/{post}/reply', [CommunityController::class, 'reply']);
    });
});
```

```php
<?php
// app/Http/Controllers/GameController.php

namespace App\Http\Controllers;

use App\Services\GameEngineService;
use App\Services\RedisStateService;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function __construct(
        private GameEngineService $engine,
        private RedisStateService $redis,
    ) {}

    public function state(Request $request) {
        // Try Redis first (fast), fallback to DB
        $round = $this->redis->getRound()
            ?? \App\Models\GameRound::latest()->first()?->toArray();

        $playerBet = null;
        if ($request->user()) {
            $playerBet = \App\Models\RoundBet::where([
                'round_id'  => $round['id'] ?? null,
                'player_id' => $request->user()->id,
            ])->first();
        }

        return response()->json([
            'round'          => $round,
            'player_bet'     => $playerBet,
            'active_players' => count($this->redis->getActivePlayers()),
            'timer_remaining'=> $this->redis->getTimerRemaining(),
        ]);
    }

    public function placeBet(Request $request) {
        $validated = $request->validate([
            'round_id'    => 'required|integer|exists:game_rounds,id',
            'bets'        => 'required|array',
            'bets.player' => 'integer|min:0',
            'bets.banker' => 'integer|min:0',
            'bets.tie'    => 'integer|min:0',
        ]);

        // Validate player/banker not both bet
        if (($validated['bets']['player'] ?? 0) > 0 && ($validated['bets']['banker'] ?? 0) > 0) {
            return response()->json(['error' => 'Cannot bet both Player and Banker'], 422);
        }

        $bet = $this->engine->placeBet(
            $validated['round_id'],
            $request->user()->id,
            $validated['bets'],
        );

        return response()->json(['bet' => $bet, 'message' => 'Bet placed!']);
    }
}
```

---

# 8. MODULE 6 — FRONTEND (VITE + BOOTSTRAP) {#module-6}

## vite.config.js

```javascript
// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.scss',
                'resources/js/app.js',
                'resources/js/game.js',
            ],
            refresh: true,
        }),
    ],
});
```

## package.json

```json
{
    "devDependencies": {
        "vite": "^5.0",
        "laravel-vite-plugin": "^1.0",
        "bootstrap": "^5.3",
        "sass": "^1.0"
    },
    "dependencies": {
        "laravel-echo": "^1.15",
        "pusher-js": "^8.0",
        "axios": "^1.6"
    }
}
```

## Blade Template

```html
{{-- resources/views/game/index.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>🐧 Lucky Puffin</title>
    @vite(['resources/css/app.scss', 'resources/js/app.js', 'resources/js/game.js'])
</head>
<body>
    <div id="game-app"
         data-player-id="{{ auth()->id() }}"
         data-player-name="{{ auth()->user()->player_name }}"
         data-balance="{{ auth()->user()->balance }}"
         data-reverb-key="{{ config('broadcasting.connections.reverb.key') }}">
        
        {{-- Game UI here --}}

    </div>
</body>
</html>
```

---

# 9. MODULE 7 — RANDOM PAIR BONUS (FUTURE) {#module-7}

## How It Works

```
If Player Hand contains SAME card as Banker Hand:
→ Example: Player has 7♥, Banker has 7♦
→ Same value (7), different suit = RANDOM PAIR!
→ Bonus: 5x the "random pair" bet amount

Different from Player Pair / Banker Pair:
→ Player Pair = same two cards in player hand (7♥ 7♦)
→ Banker Pair = same two cards in banker hand
→ Random Pair = one player card MATCHES one banker card in value
```

## Migration Addition

```php
// Add to round_bets migration:
$table->integer('bet_random_pair')->default(0);

// Add to game_rounds migration:
$table->boolean('is_random_pair')->default(false);
$table->json('random_pair_cards')->nullable(); // Which cards matched
```

## BonusService

```php
<?php
// app/Services/BonusService.php

namespace App\Services;

class BonusService
{
    /**
     * Check if any player card value matches any banker card value
     * Example: Player 7♥ vs Banker 7♦ = RANDOM PAIR!
     */
    public function checkRandomPair(array $playerCards, array $bankerCards): bool {
        $playerValues = array_column($playerCards, 'value');
        $bankerValues = array_column($bankerCards, 'value');

        return count(array_intersect($playerValues, $bankerValues)) > 0;
    }

    /**
     * Get the matching cards for display
     */
    public function getRandomPairCards(array $playerCards, array $bankerCards): array {
        $matchingValue = null;
        foreach ($playerCards as $pCard) {
            foreach ($bankerCards as $bCard) {
                if ($pCard['value'] === $bCard['value']) {
                    $matchingValue = $pCard['value'];
                    break 2;
                }
            }
        }

        if (!$matchingValue) return [];

        return [
            'player' => array_values(array_filter($playerCards, fn($c) => $c['value'] === $matchingValue))[0],
            'banker' => array_values(array_filter($bankerCards, fn($c) => $c['value'] === $matchingValue))[0],
        ];
    }

    /**
     * Calculate payout for random pair bet
     */
    public function calculateRandomPairPayout(int $betAmount, bool $isRandomPair): int {
        if (!$isRandomPair || $betAmount === 0) return 0;
        return $betAmount * config('game.payouts.random_pair'); // 5x
    }
}
```

## PayoutService (Includes Random Pair)

```php
<?php
// app/Services/PayoutService.php

namespace App\Services;

use App\Models\{GameRound, RoundBet};

class PayoutService
{
    public function calculate(RoundBet $bet, GameRound $round): int {
        $winnings = 0;
        $p = config('game.payouts');

        // Player Pair
        if ($round->is_player_pair && $bet->bet_player_pair > 0) {
            $winnings += $bet->bet_player_pair * $p['player_pair'];
        }

        // Banker Pair
        if ($round->is_banker_pair && $bet->bet_banker_pair > 0) {
            $winnings += $bet->bet_banker_pair * $p['banker_pair'];
        }

        // Random Pair (Module 7)
        if ($round->is_random_pair && $bet->bet_random_pair > 0) {
            $winnings += $bet->bet_random_pair * $p['random_pair']; // 5x
        }

        // Main bets
        $winnings += match($round->result->value) {
            'PLAYER_WINS' => $bet->bet_player * $p['player'],
            'BANKER_WINS' => $bet->bet_banker * $p['banker'],
            'TIE'         => ($bet->bet_tie * $p['tie']) + $bet->bet_player + $bet->bet_banker,
            default       => 0,
        };

        return $winnings;
    }
}
```

---

# 10. MODULE 8 — COMMUNITY THREAD (FUTURE) {#module-8}

## Features

```
- Forum-style posts (text + images)
- Nested replies (1 level deep like Reddit)
- Reactions (🎉 🔥 😭)
- "Big Win" auto-post when player wins > 10x
- Player profile links
- Moderation flags
```

## Migration

```php
Schema::create('community_posts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
    $table->foreignId('parent_id')->nullable()->constrained('community_posts')->cascadeOnDelete();
    $table->string('title', 200)->nullable();        // Only for top-level posts
    $table->text('body');
    $table->string('image_path')->nullable();
    $table->string('post_type')->default('general'); // general, big_win, strategy
    $table->integer('upvotes')->default(0);
    $table->boolean('is_pinned')->default(false);
    $table->boolean('is_flagged')->default(false);
    $table->json('reactions')->nullable();           // {fire: 3, celebrate: 5}
    $table->timestamps();
    $table->softDeletes();

    $table->index(['parent_id', 'created_at']);
    $table->index(['post_type', 'created_at']);
});
```

## Auto-Post on Big Win

```php
// In GameEngineService::calculatePayouts()
if ($netChange > $bet->total_bet * 5) {  // Won 5x or more
    CommunityPost::create([
        'player_id' => $bet->player_id,
        'body'      => "🎉 {$bet->player->player_name} just won {$netChange} WPUFF!",
        'post_type' => 'big_win',
    ]);

    // Broadcast to community feed
    broadcast(new BigWinAnnounced($bet->player->player_name, $netChange));
}
```

---

# 11. MODULE 9 — MOBILE APP INTEGRATION (FUTURE) {#module-9}

## Architecture

```
┌─────────────────┐    ┌─────────────────┐
│  iOS App        │    │  Android App    │
│  (Swift/Flutter)│    │  (Flutter)      │
└────────┬────────┘    └────────┬────────┘
         │                      │
         └──────────┬───────────┘
                    │ REST API + WebSocket
         ┌──────────▼───────────┐
         │  Laravel API         │
         │  /api/v1/*           │
         │  Sanctum Token Auth  │
         └──────────────────────┘
```

## Mobile Auth Flow

```php
// api.php
Route::post('/mobile/login',   [AuthController::class, 'mobileLogin']);
Route::post('/mobile/refresh', [AuthController::class, 'refreshToken']);
Route::post('/mobile/fcm',     [AuthController::class, 'updateFcmToken']); // Push notifications
```

```php
// AuthController - returns Sanctum token for mobile
public function mobileLogin(Request $request) {
    $validated = $request->validate([
        'guest_id' => 'required|string',
        'fcm_token'=> 'nullable|string',   // Firebase push notification token
    ]);

    $player = Player::firstOrCreate(
        ['guest_id' => $validated['guest_id']],
        ['player_name' => 'Guest' . rand(1000, 9999), 'balance' => 1000]
    );

    if ($validated['fcm_token']) {
        $player->update(['fcm_token' => $validated['fcm_token']]);
    }

    // Sanctum token for mobile API auth
    $token = $player->createToken('mobile')->plainTextToken;

    return response()->json([
        'token'  => $token,
        'player' => $player,
    ]);
}
```

## Push Notifications (Big Win Alert)

```php
// When another player wins big, notify others
use Illuminate\Support\Facades\Http;

public function sendPushNotification(string $fcmToken, string $title, string $body): void {
    Http::withToken(config('services.firebase.server_key'))
        ->post('https://fcm.googleapis.com/fcm/send', [
            'to'           => $fcmToken,
            'notification' => [
                'title' => $title,
                'body'  => $body,
                'sound' => 'default',
            ],
        ]);
}
```

## Mobile API Response Format (Consistent)

```php
// All API responses follow this format:
return response()->json([
    'success' => true,
    'data'    => $data,
    'message' => 'Optional message',
    'meta'    => [
        'timestamp' => now()->toIso8601String(),
        'version'   => '1.0',
    ],
]);
```

---

# 12. DEPLOYMENT GUIDE {#deployment}

## Server Requirements

```
- PHP 8.2+
- MySQL 8.0+ / MariaDB 10.6+
- Redis 7.0+
- Node.js 18+ (for Vite build)
- Nginx / Apache
- Supervisor (for queue workers)
```

## Installation Steps

```bash
# 1. Clone & install
git clone https://github.com/yourrepo/lucky-puffin.git
composer install --optimize-autoloader --no-dev
npm install && npm run build

# 2. Environment
cp .env.example .env
php artisan key:generate

# 3. Database
php artisan migrate --force
php artisan db:seed   # Optional: seed test players

# 4. Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Start queue worker (processes timer jobs)
php artisan queue:work redis --queue=game,default

# 6. Start WebSocket server
php artisan reverb:start --port=8080

# 7. (Optional) Laravel Horizon for queue monitoring
php artisan horizon
```

## Supervisor Config (Keep Workers Running)

```ini
; /etc/supervisor/conf.d/lucky-puffin.conf

[program:laravel-queue]
command=php /var/www/lucky-puffin/artisan queue:work redis --queue=game,default --sleep=1
autostart=true
autorestart=true
numprocs=2

[program:laravel-reverb]
command=php /var/www/lucky-puffin/artisan reverb:start --port=8080
autostart=true
autorestart=true
numprocs=1
```

---

# 13. MIGRATION CHECKLIST {#checklist}

## Phase 1 — Setup (Week 1)

```
□ Laravel 11 fresh install
□ Configure .env (DB, Redis, Reverb)
□ Run all migrations
□ Install Reverb, Echo, Sanctum
□ Test WebSocket connection in browser
□ npm run dev works
```

## Phase 2 — Core Game (Week 2)

```
□ GameEngineService with Redis state
□ DeckService + PayoutService
□ All 5 broadcast events working
□ Timer job with queue worker
□ placeBet endpoint
□ Frontend connects via Echo
□ Cards animate one by one
□ Result shows on correct player
```

## Phase 3 — Polish (Week 3)

```
□ Guest login (Sanctum token)
□ Hourly bonus
□ Leaderboard
□ Activity feed via broadcast
□ Auto-clear bets on new round
□ Non-betting player overlay closes
□ Mobile-responsive layout
```

## Phase 4 — Future Modules

```
□ Module 7: Random Pair Bonus UI + logic
□ Module 8: Community thread + big win auto-post
□ Module 9: Flutter app + FCM push notifications
```

---

# QUICK REFERENCE CARD

```
Start Dev Server:    php artisan serve
Start WebSocket:     php artisan reverb:start
Start Queue Worker:  php artisan queue:work redis --queue=game,default
Start Vite:          npm run dev

Build for Prod:      npm run build
Clear Cache:         php artisan optimize:clear
Restart Queue:       php artisan queue:restart

Monitor Queue:       php artisan horizon
Monitor WS:          php artisan reverb:start --debug

Redis Flush (DEV):   php artisan tinker → Redis::flushdb()
```

---

*Lucky Puffin Architecture Guide v2.0*
*Stack: Laravel 11 · PHP 8.2 · Redis · Ratchet/Reverb · Bootstrap · Vite*