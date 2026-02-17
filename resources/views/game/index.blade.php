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
{{--
    data-* attributes pass server values to game.js on boot.
    Player is resolved client-side via guestLogin API call before this page renders.
    In a real deploy, you'd inject these after Sanctum auth middleware.
--}}
<div id="game-app"
     data-player-id="{{ auth()->id() ?? 0 }}"
     data-player-name="{{ auth()->user()?->player_name ?? 'Guest' }}"
     data-balance="{{ auth()->user()?->balance ?? 0 }}">

    <div class="container-fluid py-3" style="max-width:1200px">

        {{-- ── Header ──────────────────────────────────────────── --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0 text-warning fw-bold">🐧 Lucky Puffin</h4>
            <div class="d-flex gap-3 align-items-center">
                <span class="text-white-50 small">
                    Players: <span id="player-count" class="text-white fw-bold">0</span>
                </span>
                <span class="badge bg-success fs-6">
                    💰 <span id="player-balance">0</span> WPUFF
                </span>
            </div>
        </div>

        <div class="row g-3">

            {{-- ── Left: Leaderboard + Activity ───────────────── --}}
            <div class="col-lg-3 d-none d-lg-block">

                <div class="card bg-dark border-secondary mb-3">
                    <div class="card-header text-warning fw-bold small">🏆 Leaderboard</div>
                    <div class="card-body p-2" id="leaderboard">
                        <div class="text-white-50 small text-center py-2">Loading...</div>
                    </div>
                </div>

                <div class="card bg-dark border-secondary">
                    <div class="card-header text-info fw-bold small">⚡ Activity</div>
                    <div class="card-body p-2" id="activity-feed">
                        <div class="text-white-50 small text-center py-2">No activity yet</div>
                    </div>
                </div>

            </div>

            {{-- ── Centre: Game Table ──────────────────────────── --}}
            <div class="col-lg-6">

                {{-- Timer --}}
                <div class="text-center mb-2">
                    <span id="bet-timer" class="badge bg-secondary fs-6 px-4 py-2">
                        <i class="bi bi-clock-fill"></i> Waiting for players...
                    </span>
                </div>

                {{-- Betting boxes --}}
                <div class="row g-2 mb-3">

                    {{-- Player --}}
                    <div class="col-5">
                        <div class="betting-box p-3 text-center" data-bet-type="player"
                             onclick="handleBetClick(this)">
                            <div class="small text-white-50 mb-1">PLAYER</div>
                            <div class="fs-4 fw-bold text-white" id="player-total">0</div>
                            <div class="small text-white-50 mt-1">
                                Bet: <span id="player-total">0</span>
                            </div>
                        </div>
                    </div>

                    {{-- Tie --}}
                    <div class="col-2">
                        <div class="betting-box p-3 text-center" data-bet-type="tie"
                             onclick="handleBetClick(this)">
                            <div class="small text-warning">TIE</div>
                            <div class="small text-white-50">8:1</div>
                            <div class="small mt-1"><span id="tie-total">0</span></div>
                        </div>
                    </div>

                    {{-- Banker --}}
                    <div class="col-5">
                        <div class="betting-box p-3 text-center" data-bet-type="banker"
                             onclick="handleBetClick(this)">
                            <div class="small text-white-50 mb-1">BANKER</div>
                            <div class="fs-4 fw-bold text-white" id="banker-total">0</div>
                            <div class="small text-white-50 mt-1">
                                Bet: <span id="banker-total">0</span>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Pair side bets --}}
                <div class="row g-2 mb-3">
                    <div class="col-4">
                        <div class="betting-box p-2 text-center" data-bet-type="playerPair"
                             onclick="handleBetClick(this)">
                            <div class="small text-white-50">P. Pair 10:1</div>
                            <div class="small fw-bold"><span id="player-pair-total">0</span></div>
                        </div>
                    </div>
                    <div class="col-4">
                        {{-- Module 7: Random Pair --}}
                        <div class="betting-box p-2 text-center" data-bet-type="randomPair"
                             onclick="handleBetClick(this)">
                            <div class="small text-warning">Rnd Pair 4:1</div>
                            <div class="small fw-bold"><span id="random-pair-total">0</span></div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="betting-box p-2 text-center" data-bet-type="bankerPair"
                             onclick="handleBetClick(this)">
                            <div class="small text-white-50">B. Pair 10:1</div>
                            <div class="small fw-bold"><span id="banker-pair-total">0</span></div>
                        </div>
                    </div>
                </div>

                {{-- Chips --}}
                <div class="d-flex justify-content-center gap-2 mb-3">
                    @foreach([10, 50, 100, 500, 1000] as $value)
                        <div class="chip" data-value="{{ $value }}" onclick="selectChip({{ $value }})">
                            {{ $value }}
                        </div>
                    @endforeach
                </div>

                {{-- Actions --}}
                <div class="d-flex gap-2 justify-content-center">
                    <button class="btn btn-outline-danger btn-sm" onclick="clearAllBets()">
                        <i class="bi bi-x-circle"></i> Clear Bets
                    </button>
                </div>

            </div>

            {{-- ── Right: Player info --}}
            <div class="col-lg-3 d-none d-lg-block">
                <div class="card bg-dark border-secondary">
                    <div class="card-header text-white-50 small">👤 Your Stats</div>
                    <div class="card-body text-center">
                        <div class="fs-5 fw-bold text-warning mb-1">
                            <span id="player-balance-side">–</span> WPUFF
                        </div>
                        <div class="small text-white-50">Current Balance</div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- ── Dealing Overlay ──────────────────────────────────────── --}}
    <div id="card-dealing-overlay">
        <h3 class="text-warning mb-4">🃏 Dealing Cards...</h3>
        <div class="row g-4 w-100 justify-content-center" style="max-width:700px">
            <div class="col-6 text-center">
                <div class="text-white-50 small mb-2">PLAYER</div>
                <div id="player-hand" class="d-flex flex-wrap justify-content-center gap-1"></div>
                <div class="mt-2 fw-bold fs-4 text-white">
                    Total: <span id="player-total-score">?</span>
                </div>
            </div>
            <div class="col-6 text-center">
                <div class="text-white-50 small mb-2">BANKER</div>
                <div id="banker-hand" class="d-flex flex-wrap justify-content-center gap-1"></div>
                <div class="mt-2 fw-bold fs-4 text-white">
                    Total: <span id="banker-total-score">?</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Result Overlay ───────────────────────────────────────── --}}
    <div id="result-overlay">
        <div class="text-center">
            <h1 class="display-4 fw-bold text-warning mb-2" id="result-title">–</h1>
            <div class="fs-4 mb-4" id="result-details"></div>
            <div class="text-white-50">
                Next round in <span id="result-countdown" class="text-white fw-bold">5s</span>
            </div>
        </div>
    </div>

    {{-- ── Toast container ──────────────────────────────────────── --}}
    <div id="toast-container"></div>

</div>
</body>
</html>
