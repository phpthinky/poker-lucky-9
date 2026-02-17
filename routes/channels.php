<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels — Lucky Puffin
|--------------------------------------------------------------------------
|
| game-table    → Public channel. All players auto-subscribed.
|               → Receives: round.started, timer.tick, cards.dealt,
|                           bet.placed, round.finished
|
| player.{id}  → Private channel. Only that player.
|               → Receives: round.result (their win/loss + new balance)
|
| Future channels:
|   community   → Public chat/feed (Module 8)
|   admin        → Admin dashboard live updates
|--------------------------------------------------------------------------
*/

// Public channel — no auth needed, anyone can subscribe
// (Handled automatically by Laravel Reverb for Channel type)

// Private player channel — only that player can subscribe
Broadcast::channel('player.{id}', function ($user, int $id) {
    // Verify the authenticated user owns this channel
    return (int) $user->id === $id;
});

// Future: Private admin channel
// Broadcast::channel('admin', function ($user) {
//     return $user->is_admin;
// });
