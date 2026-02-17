# 🐧 Lucky Puffin — Setup Guide

## Requirements
- PHP 8.2+
- MySQL 8.0+ / MariaDB 10.6+
- Redis 7+
- Node.js 18+
- Composer 2+

---

## Install Steps

### 1. Clone & Install Dependencies
```bash
composer install
npm install
```

### 2. Environment
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and set:
```env
DB_DATABASE=lucky_puffin
DB_USERNAME=your_user
DB_PASSWORD=your_pass

REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

### 3. Database
```bash
mysql -u root -p -e "CREATE DATABASE lucky_puffin CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate
```

### 4. Build Frontend
```bash
npm run dev       # Development (with hot reload)
npm run build     # Production
```

### 5. Start Services (3 terminals)

**Terminal 1 — Laravel app:**
```bash
php artisan serve
```

**Terminal 2 — WebSocket server:**
```bash
php artisan reverb:start --port=8080
```

**Terminal 3 — Queue worker (handles timer jobs):**
```bash
php artisan queue:work redis --queue=game,default
```

---

## Production (Supervisor)

```ini
; /etc/supervisor/conf.d/lucky-puffin.conf

[program:lp-queue]
command=php /var/www/lucky-puffin/artisan queue:work redis --queue=game,default
autostart=true
autorestart=true
numprocs=2
stderr_logfile=/var/log/lp-queue.err.log

[program:lp-reverb]
command=php /var/www/lucky-puffin/artisan reverb:start --port=8080
autostart=true
autorestart=true
numprocs=1
stderr_logfile=/var/log/lp-reverb.err.log
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all
```

---

## API Quick Reference

| Method | Route | Auth | Description |
|--------|-------|------|-------------|
| POST | `/api/v1/guest/login` | — | Create/get guest player + token |
| POST | `/api/v1/login` | — | Email login |
| POST | `/api/v1/register` | — | Register account |
| GET  | `/api/v1/game/state` | Bearer | Full game state |
| POST | `/api/v1/game/bet` | Bearer | Place bet |
| POST | `/api/v1/game/clear` | Bearer | Clear bets + refund |
| GET  | `/api/v1/player` | Bearer | Player profile |
| POST | `/api/v1/player/reset` | Bearer | Reset balance to 1000 |
| GET  | `/api/v1/leaderboard` | — | Top 10 players |
| POST | `/api/v1/logout` | Bearer | Revoke token |

---

## WebSocket Channels

| Channel | Type | Events |
|---------|------|--------|
| `game-table` | Public | `round.started`, `timer.tick`, `bet.placed`, `cards.dealt`, `round.finished` |
| `player.{id}` | Private | `round.result` (win/loss + balance) |

---

## File Overview

```
app/
├── Http/Controllers/
│   ├── AuthController.php      Guest login, register, login, logout
│   ├── GameController.php      state, placeBet, clearBet, profile, resetBalance
│   └── LeaderboardController.php
│
├── Models/
│   ├── Player.php
│   ├── GameRound.php           + RoundStatus enum
│   ├── RoundBet.php
│   ├── ActivityFeed.php
│   └── BonusClaim.php
│
├── Services/
│   ├── GameEngineService.php   Core game logic (round lifecycle, payouts)
│   ├── RedisStateService.php   In-memory round state + active players
│   ├── DeckService.php         Card dealing, pair detection
│   └── PayoutService.php       Payout calculation
│
└── Providers/
    └── AppServiceProvider.php  Service container bindings

database/migrations/
    000001_create_players_table.php
    000002_create_game_rounds_table.php
    000003_create_round_bets_table.php
    000004_create_activity_and_bonus_tables.php

routes/
    api.php         REST API routes
    web.php         Game page
    channels.php    WebSocket channel auth

resources/
    js/app.js       Bootstrap + Echo init
    js/game.js      Game UI + WebSocket listeners
    css/app.scss    Bootstrap + game styles
    views/game/index.blade.php
```

---

## Next Steps

Once this is working:

1. **Events** — wire up `RoundStarted`, `TimerTick`, `CardsDealt`, `BetPlaced`, `PlayerResult`
2. **Timer Job** — `ProcessRoundTimer` dispatched to queue when round starts
3. **Module 7** — Random Pair bonus UI
4. **Module 8** — Community thread
5. **Module 9** — Flutter mobile app
