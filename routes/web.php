<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Lucky Puffin
|--------------------------------------------------------------------------
*/

// Main game page — serves the Blade view
// All game logic is handled via API + WebSocket
Route::get('/', function () {
    return view('game.index');
})->name('game.index');

// Future: community page (Module 8)
// Route::get('/community', function () {
//     return view('community.index');
// })->name('community.index');
