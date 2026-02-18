# 🔌 Lucky Puffin — Event + Job Wiring Guide

## Full Data Flow

```
Player clicks chip
    │
    ▼
[Frontend] handleBetClick()
    │  POST /api/v1/game/bet
    ▼
[GameController::placeBet()]
    │  Validates bet, checks balance
    ▼
[GameEngineService::placeBet()]
    │  DB: upsert round_bets, decrement balance
    │  Redis: addActivePlayer()
    │  ── If first bet on 'waiting' round:
    │     GameEngineService::startBetting()
    │         DB: update round_status = 'betting', started_at = now()
    │         Redis: setTimerEndsAt()
    │         Dispatch: ProcessRoundTimer → 'game' queue
    │
    │  broadcast(BetPlaced) → game-table channel
    │      → All other players: addActivityItem("Player bet X WPUFF")
    │
    ▼
[Queue Worker picks up ProcessRoundTimer]
    │
    │  broadcast(RoundStarted) → game-table
    │      → All clients: clearBets(), enableBetting(), updateTimerDisplay(20)
    │
    │  Loop seconds 20 → 0:
    │      broadcast(TimerTick) → game-table
    │          → All clients: updateTimerDisplay(N)
    │      sleep(1)
    │
    │  DeckService::deal()           → player/banker cards
    │  DeckService::calculateTotal() → 0-9 values
    │  DeckService::isPair()         → player/banker pair flags
    │  DeckService::isRandomPair()   → Module 7 flag
    │
    │  DB: update game_rounds (cards, result, status='dealing', dealing_ends_at)
    │  Redis: updateRoundField()
    │
    │  broadcast(CardsDealt) → game-table
    │      → All clients: showSharedCards() — 1 card per second animation
    │
    │  sleep(dealing_duration = 5s)
    │
    │  For each bet:
    │      PayoutService::calculate()
    │      DB: update round_bets.total_won, increment balance, games_played
    │      broadcast(PlayerResult) → player.{id} PRIVATE
    │          → That player only: showResult(), syncBalance()
    │
    │  DB: update round_status = 'finished', finished_at = now()
    │
    │  broadcast(RoundFinished) → game-table
    │      → Non-betting players: close overlay, show "Next round in Xs"
    │
    │  sleep(result_duration = 5s)
    │
    │  GameRound::create(status='waiting')
    │  Redis: setRound(), clearActivePlayers(), clearTimer()
    │
    │  broadcast(RoundStarted, status='waiting') → game-table
    │      → All clients: clearBets(), enableBetting()
    │
    ▼
[Back to start — waiting for first bet]
```

---

## Files Added This Module

```
app/Events/
    RoundStarted.php     round_id, timer_seconds, status
    TimerTick.php        round_id, seconds_remaining
    BetPlaced.php        round_id, player_name, total_bet, active_players
    CardsDealt.php       round_id, all card data, result, pair flags
    PlayerResult.php     PRIVATE — result, profit, new_balance
    RoundFinished.php    round_id, result, totals, next_round_in

app/Jobs/
    ProcessRoundTimer.php  Full round lifecycle (countdown → deal → payouts → reset)

app/Console/Commands/
    StartGame.php          php artisan game:start [--force]

app/Services/
    GameEngineService.php  Updated: events + job dispatch wired in

resources/js/
    game.js               Updated: full WS event handling, no polling
```

---

## Queue Setup

Events and the timer job run on Redis queues.

### Required .env
```env
QUEUE_CONNECTION=redis
BROADCAST_CONNECTION=reverb
```

### Start worker
```bash
# Development
php artisan queue:work redis --queue=game,default --verbose

# Production (Supervisor handles restart)
php artisan queue:work redis --queue=game,default --sleep=1 --tries=1
```

### Why `--tries=1`?
The timer job must NOT retry on failure.
A retry could cause double dealing or double payouts.
If it fails, the `failed()` method resets the round to 'waiting'.

---

## Channel Reference

| Channel | Type | Auth | Events |
|---------|------|------|--------|
| `game-table` | Public | None | `round.started` `timer.tick` `bet.placed` `cards.dealt` `round.finished` |
| `player.{id}` | Private | Sanctum | `round.result` |

### channels.php (already in Module 1)
```php
Broadcast::channel('player.{id}', function ($user, int $id) {
    return (int) $user->id === $id;
});
```

---

## Startup Sequence

### First boot
```bash
# 1. Start Reverb WebSocket server
php artisan reverb:start --port=8080

# 2. Start queue worker
php artisan queue:work redis --queue=game,default

# 3. Bootstrap the game (creates waiting round)
php artisan game:start

# 4. Open browser → visit game page → place first bet → timer starts!
```

### After deployment / server restart
```bash
php artisan game:start    # Safe — won't create duplicate if round exists
# or
php artisan game:start --force   # Reset everything and start fresh
```

---

## Event Payload Examples

### round.started (public)
```json
{ "round_id": 42, "timer_seconds": 20, "status": "betting" }
```

### timer.tick (public)
```json
{ "round_id": 42, "seconds_remaining": 15 }
```

### bet.placed (public)
```json
{ "round_id": 42, "player_name": "Guest1234", "total_bet": 100, "active_players": 3 }
```

### cards.dealt (public)
```json
{
  "round_id": 42,
  "player_cards": [{"suit":"♥","value":"7","display":"7♥"}, {"suit":"♠","value":"2","display":"2♠"}],
  "banker_cards": [{"suit":"♦","value":"K","display":"K♦"}, {"suit":"♣","value":"5","display":"5♣"}],
  "player_total": 9,
  "banker_total": 5,
  "result": "PLAYER_WINS",
  "is_player_pair": false,
  "is_banker_pair": false,
  "is_random_pair": false
}
```

### round.result (PRIVATE — player.42 only)
```json
{
  "result": "PLAYER_WINS",
  "total_bet": 100,
  "total_won": 200,
  "profit": 100,
  "new_balance": 1100
}
```

### round.finished (public)
```json
{ "round_id": 42, "result": "PLAYER_WINS", "player_total": 9, "banker_total": 5, "next_round_in": 5 }
```

---

## Troubleshooting

### Timer doesn't start
- Is queue worker running? `php artisan queue:work redis --queue=game,default`
- Did first bet go through? Check `round_bets` table
- Check logs: `tail -f storage/logs/laravel.log | grep Timer`

### All players don't see cards
- Is Reverb running? `php artisan reverb:start`
- Check browser console for WS connection errors
- Verify `.env` REVERB keys match

### Balance is wrong
- Balance deducted at bet time, added back at payout
- Check `round_bets.total_won` is being set
- Run: `SELECT * FROM round_bets WHERE total_won = 0 AND round_id = X`

### Round stuck in 'dealing'
```bash
php artisan game:start --force   # Resets stuck rounds
```

### Duplicate timer jobs
- Set `--tries=1` on queue worker
- `ProcessRoundTimer` has `$uniqueId` to prevent queuing same round twice
