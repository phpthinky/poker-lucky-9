🐧 Lucky Puffin

Lucky Puffin is a real-time multiplayer casino-style card game built with PHP 8.2 and MySQL.

It features synchronized server-controlled rounds, automatic card dealing, live betting, and leaderboard tracking — all running without manual refresh logic conflicts.

🎮 Game Overview

Lucky Puffin is a Lucky 9 / baccarat-inspired betting game where players can bet on:

🟦 Player

🟥 Banker

🟩 Tie

🟦 Player Pair

🟥 Banker Pair

🎲 Random Pair (custom feature)

All rounds are globally synchronized across connected users.

⚙ Core Features
⏱ Server-Controlled Timer

Single global round timer

No client-side countdown manipulation

Betting → Dealing → Result → Auto New Round

🎴 Automatic Card Dealing

No "Deal" button

Cards are dealt automatically when betting phase ends

All players see the exact same cards

💰 Instant Betting System

Select chip

Click betting area

Bet is instantly recorded

Balance updates immediately

Total pooled bet updates live

🏆 Leaderboard

Top players displayed in real-time

Based on total winnings or balance

Auto-refreshes during active rounds

🔄 Fully Automatic Round Flow

Betting Phase (20 seconds)

Auto Deal

Result Phase (5 seconds)

Auto Start New Round

No manual restart required.

🧱 Technical Stack

PHP 8.2

MySQL

PDO (Singleton Database class)

Bootstrap 5.3 (Mobile-first layout)

AJAX Polling (real-time sync)

JSON API architecture

🗂 Project Structure
/config
/classes
    Database.php
/api
    getGameState.php
    placeBet.php
    dealCards.php
/public
    index.php
/assets
    css/
    js/

🧠 Architecture Highlights
Server-Authoritative Timing

Timer is calculated using:

$timeElapsed = time() - strtotime($round['started_at']);
$timeRemaining = 20 - $timeElapsed;


This prevents:

Timer desync

Multiple deal triggers

Client manipulation

🗄 Database Design (Simplified)
rounds

id

round_number

status (betting / dealing / finished)

started_at

result

player_cards

banker_cards

bets

id

round_id

user_id

bet_type

amount

users

id

username

balance

🚀 Installation

Clone repository:

git clone https://github.com/yourusername/lucky-puffin.git


Create database

Update .env or config with DB credentials

Import SQL schema

Run on local server:

php -S localhost:8000


Open in browser:

http://localhost:8000

🔐 Security Notes

All betting logic is processed server-side

No card logic is handled in JavaScript

Database uses prepared statements (PDO)

Client cannot manipulate round results

🛣 Roadmap
v1.1

Shared total bet display per side

Card flip animation sync

Better pooled bet visibility

Round history panel

v1.2

WebSocket real-time upgrade

Multiple deck shoe system

Admin dashboard

Community thread system (Laravel 11 integration)

⚠ Disclaimer

Lucky Puffin is currently built as a game simulation system.
Ensure compliance with your local regulations before deploying publicly.

👑 Project Status

✅ Multiplayer synchronized
✅ Automatic round flow
✅ Real-time betting
🔜 WebSocket upgrade

Lucky Puffin v1.0

Multiplayer foundation complete. 🐧🔥