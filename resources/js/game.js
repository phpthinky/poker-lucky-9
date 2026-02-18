/**
 * Lucky Puffin — game.js (Laravel Mix / Webpack)
 *
 * THE FIX: app.js and game.js are separate Webpack bundles.
 * window.Echo is created in app.js but game.js may run before it finishes.
 *
 * Solution: wait for 'echo:ready' custom event dispatched by app.js,
 * with a 100ms polling fallback in case scripts load out of order.
 */

// ── State ─────────────────────────────────────────────────────────────────────
let playerId        = null;
let playerBalance   = 0;
let playerName      = null;

let currentRound    = null;
let currentBets     = { player: 0, banker: 0, tie: 0, playerPair: 0, bankerPair: 0, randomPair: 0 };
let selectedChip    = 0;
let hasBetThisRound = false;
let dealingShown    = false;

const API_BASE = '/api/v1';

// ── Entry point ───────────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', () => {
    readPlayerData();

    if (window.echoReady && window.Echo) {
        // app.js already finished — boot immediately
        boot();
    } else {
        // Wait for app.js to signal Echo is ready
        document.addEventListener('echo:ready', boot, { once: true });

        // Fallback polling: check every 100ms, give up after 5s
        let attempts = 0;
        const poll = setInterval(() => {
            if (window.Echo) {
                clearInterval(poll);
                document.removeEventListener('echo:ready', boot);
                boot();
            } else if (++attempts > 50) {
                clearInterval(poll);
                console.error('[Game] Echo not available after 5s — check app.js loaded before game.js');
                setTimerLabel('WebSocket error — please refresh');
            }
        }, 100);
    }
});

function readPlayerData() {
    const app = document.getElementById('game-app');
    if (!app) return;
    playerId      = parseInt(app.dataset.playerId)  || null;
    playerName    = app.dataset.playerName           || 'Guest';
    playerBalance = parseInt(app.dataset.balance)    || 0;
    updateBalanceDisplay();
}

function boot() {
    console.log('[Game] Echo ready — subscribing to channels');
    subscribeToGameTable();
    subscribeToPlayerChannel();
    fetchInitialState();
}

// ── WebSocket: Public channel ─────────────────────────────────────────────────

function subscribeToGameTable() {
    window.Echo.channel('game-table')

        .listen('.round.started', (data) => {
            console.log('[WS] round.started', data);
            const isNewRound = !currentRound || currentRound.round_id !== data.round_id;
            currentRound     = data;
            dealingShown     = false;

            if (isNewRound) {
                clearBets();
                hideOverlays();
            }

            if (data.status === 'betting') {
                enableBetting();
                updateTimerDisplay(data.timer_seconds);
            } else {
                setTimerLabel('Waiting for first bet...');
                enableBetting();
            }
        })

        .listen('.timer.tick', (data) => {
            updateTimerDisplay(data.seconds_remaining);
        })

        .listen('.bet.placed', (data) => {
            addActivityItem('\uD83C\uDFB2 ' + data.player_name + ' bet ' + data.total_bet + ' WPUFF');
            setPlayerCount(data.active_players);
        })

        .listen('.cards.dealt', (data) => {
            console.log('[WS] cards.dealt', data);
            if (dealingShown) return;
            dealingShown = true;
            currentRound = Object.assign({}, currentRound, data);
            disableBetting();
            setTimerLabel('Dealing cards...');
            showSharedCards(data);

            if (!hasBetThisRound) {
                const pLen   = (data.player_cards || data.playerCards || []).length;
                const bLen   = (data.banker_cards || data.bankerCards || []).length;
                const waitMs = (Math.max(pLen, bLen) * 1000) + 1500 + 2000;
                setTimeout(hideOverlays, waitMs);
            }
        })

        .listen('.round.finished', (data) => {
            console.log('[WS] round.finished', data);
            showNextRoundCountdown(data.next_round_in);
        });
}

// ── WebSocket: Private player channel ────────────────────────────────────────

function subscribeToPlayerChannel() {
    if (!playerId) return;

    window.Echo.private('player.' + playerId)
        .listen('.round.result', (data) => {
            console.log('[WS] round.result', data);
            playerBalance = data.new_balance;
            updateBalanceDisplay();

            const pLen   = (currentRound && currentRound.player_cards) ? currentRound.player_cards.length : 3;
            const bLen   = (currentRound && currentRound.banker_cards) ? currentRound.banker_cards.length : 3;
            const animMs = (Math.max(pLen, bLen) * 1000) + 1500;
            setTimeout(function () { showResult(data); }, animMs);
        });
}

// ── REST: Initial page load ───────────────────────────────────────────────────

async function fetchInitialState() {
    try {
        const res   = await window.axios.get(API_BASE + '/game/state');
        const data  = res.data;
        const round = data.current_round;

        if (round) {
            currentRound = round;
            dealingShown = false;

            if (round.round_status === 'betting') {
                enableBetting();
                updateTimerDisplay(round.timer_remaining || 0);
            }

            if (round.round_status === 'dealing' && round.player_cards && round.player_cards.length) {
                dealingShown = true;
                disableBetting();
                showSharedCards({
                    player_cards: round.player_cards,
                    banker_cards: round.banker_cards,
                    player_total: round.player_total,
                    banker_total: round.banker_total,
                });
            }
        }

        if (data.leaderboard)     renderLeaderboard(data.leaderboard, data.active_players || {});
        if (data.recent_activity) renderActivityFeed(data.recent_activity);
        setPlayerCount(Object.keys(data.active_players || {}).length);

    } catch (err) {
        console.error('[Init] Failed to load game state:', err);
        setTimerLabel('Connection error — refresh to retry');
    }
}

// ── Betting ───────────────────────────────────────────────────────────────────

async function handleBetClick(box) {
    if (!currentRound) return showToast('Loading...');

    const status = currentRound.round_status || currentRound.status;
    if (status !== 'waiting' && status !== 'betting') {
        return showToast('Wait for the next round!');
    }
    if (selectedChip === 0) return showToast('Select a chip first!');

    const betType = box.dataset.betType;
    if (betType === 'player' && currentBets.banker > 0) return showToast('Cannot bet Player and Banker!');
    if (betType === 'banker' && currentBets.player > 0) return showToast('Cannot bet Player and Banker!');

    const projected = Object.assign({}, currentBets, { [betType]: currentBets[betType] + selectedChip });
    const newTotal  = Object.values(projected).reduce(function (s, v) { return s + v; }, 0);
    if (newTotal > playerBalance) return showToast('Insufficient balance!');

    // Optimistic update
    currentBets[betType] += selectedChip;
    playerBalance        -= selectedChip;
    updateBetDisplay(betType);
    updateBalanceDisplay();
    hasBetThisRound = true;

    try {
        await window.axios.post(API_BASE + '/game/bet', {
            round_id: currentRound.id || currentRound.round_id,
            bets:     currentBets,
        });
        showToast('+' + selectedChip + ' on ' + betType.toUpperCase());
    } catch (err) {
        // Roll back
        currentBets[betType] -= selectedChip;
        playerBalance        += selectedChip;
        hasBetThisRound       = Object.values(currentBets).some(function (v) { return v > 0; });
        updateBetDisplay(betType);
        updateBalanceDisplay();
        const msg = err.response && err.response.data && err.response.data.message
            ? err.response.data.message
            : 'Bet failed — try again';
        showToast(msg);
        console.error('[Bet]', err);
    }
}

async function clearAllBets() {
    const status = (currentRound && (currentRound.round_status || currentRound.status));
    if (status !== 'betting') return showToast('Cannot clear bets now!');

    const total = Object.values(currentBets).reduce(function (s, v) { return s + v; }, 0);
    if (total === 0) return;

    try {
        const res = await window.axios.post(API_BASE + '/game/clear', {
            round_id: currentRound.id || currentRound.round_id,
        });
        playerBalance = res.data.balance;
        clearBets();
        updateBalanceDisplay();
        showToast('Cleared! ' + total + ' WPUFF refunded');
    } catch (err) {
        showToast('Could not clear bets');
    }
}

// ── Card animation ────────────────────────────────────────────────────────────

function showSharedCards(data) {
    const overlay = document.getElementById('card-dealing-overlay');
    const pHand   = document.getElementById('player-hand');
    const bHand   = document.getElementById('banker-hand');
    if (!overlay || !pHand || !bHand) return;

    overlay.classList.add('active');
    pHand.innerHTML = '';
    bHand.innerHTML = '';
    var pScore = document.getElementById('player-total-score');
    var bScore = document.getElementById('banker-total-score');
    if (pScore) pScore.textContent = '?';
    if (bScore) bScore.textContent = '?';

    const DELAY  = 1000;
    const pCards = data.player_cards || data.playerCards || [];
    const bCards = data.banker_cards || data.bankerCards || [];

    pCards.forEach(function (card, i) {
        setTimeout(function () { appendCard(pHand, card); }, i * DELAY);
    });
    bCards.forEach(function (card, i) {
        setTimeout(function () { appendCard(bHand, card); }, (i * DELAY) + 500);
    });

    const totalMs = Math.max(pCards.length, bCards.length) * DELAY + 800;
    setTimeout(function () {
        if (pScore) pScore.textContent = data.player_total != null ? data.player_total : (data.playerTotal || '?');
        if (bScore) bScore.textContent = data.banker_total != null ? data.banker_total : (data.bankerTotal || '?');
    }, totalMs);
}

function appendCard(container, card) {
    const el       = document.createElement('div');
    el.className   = 'card-placeholder';
    el.textContent = card.display;
    el.style.color = ['♥', '♦'].indexOf(card.suit) !== -1 ? '#dc3545' : '#212529';
    container.appendChild(el);
}

function showResult(data) {
    hideOverlays();

    const resultEl  = document.getElementById('result-overlay');
    const titleEl   = document.getElementById('result-title');
    const detailsEl = document.getElementById('result-details');
    if (!resultEl) return;

    resultEl.classList.add('active');
    if (titleEl)   titleEl.textContent = (data.result || '').replace(/_/g, ' ');

    var profit = data.profit || 0;
    if (detailsEl) {
        detailsEl.textContent = profit > 0
            ? 'You won +' + profit + ' WPUFF \uD83C\uDF89'
            : profit < 0
                ? 'You lost ' + Math.abs(profit) + ' WPUFF \uD83D\uDE22'
                : 'Tie \u2014 bet returned';
    }

    startCountdown('result-countdown', 5, function () {
        resultEl.classList.remove('active');
    });
}

function showNextRoundCountdown(seconds) {
    var wrapper = document.getElementById('next-round-wrapper');
    if (!wrapper) return;
    wrapper.classList.remove('d-none');
    startCountdown('next-round-seconds', seconds, function () {
        wrapper.classList.add('d-none');
    });
}

function hideOverlays() {
    var d = document.getElementById('card-dealing-overlay');
    var r = document.getElementById('result-overlay');
    if (d) d.classList.remove('active');
    if (r) r.classList.remove('active');
}

// ── UI helpers ────────────────────────────────────────────────────────────────

function clearBets() {
    Object.keys(currentBets).forEach(function (k) { currentBets[k] = 0; updateBetDisplay(k); });
    document.querySelectorAll('.betting-box').forEach(function (b) { b.classList.remove('disabled'); });
    hasBetThisRound = false;
}

function enableBetting()  {
    document.querySelectorAll('.betting-box').forEach(function (b) { b.classList.remove('disabled'); });
}
function disableBetting() {
    document.querySelectorAll('.betting-box').forEach(function (b) { b.classList.add('disabled'); });
}

function updateTimerDisplay(seconds) {
    var el = document.getElementById('bet-timer');
    if (!el) return;
    el.innerHTML = '<i class="bi bi-clock-fill"></i> Time: <span class="fw-bold">' + seconds + '</span>s';
    el.classList.remove('text-success', 'text-warning', 'text-danger');
    if (seconds <= 5)       el.classList.add('text-danger');
    else if (seconds <= 10) el.classList.add('text-warning');
    else                    el.classList.add('text-success');
}

function setTimerLabel(text) {
    var el = document.getElementById('bet-timer');
    if (el) el.innerHTML = '<i class="bi bi-clock-fill"></i> ' + text;
}

var BET_DISPLAY_MAP = {
    player:     'player-total',
    banker:     'banker-total',
    tie:        'tie-total',
    playerPair: 'player-pair-total',
    bankerPair: 'banker-pair-total',
    randomPair: 'random-pair-total',
};

function updateBetDisplay(betType) {
    var el = document.getElementById(BET_DISPLAY_MAP[betType]);
    if (el) el.textContent = currentBets[betType];
}

function updateBalanceDisplay() {
    ['player-balance', 'player-balance-side'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) el.textContent = Number(playerBalance).toLocaleString();
    });
}

function setPlayerCount(n) {
    var el = document.getElementById('player-count');
    if (el) el.textContent = n;
}

function renderLeaderboard(leaders, activePlayers) {
    var el = document.getElementById('leaderboard');
    if (!el) return;
    var activeIds = Object.keys(activePlayers || {}).map(Number);
    el.innerHTML = leaders.map(function (p, i) {
        var isMe     = p.id === playerId;
        var isActive = activeIds.indexOf(p.id) !== -1;
        return '<div class="d-flex justify-content-between align-items-center py-1 border-bottom border-secondary small">'
            + '<span class="text-warning fw-bold">#' + (i + 1) + '</span>'
            + '<span class="' + (isMe ? 'text-warning fw-bold' : 'text-white') + '">'
            + escHtml(p.player_name) + (isActive ? ' \uD83D\uDFE2' : '')
            + '</span>'
            + '<span class="text-success fw-bold">' + Number(p.balance).toLocaleString() + '</span>'
            + '</div>';
    }).join('');
}

function renderActivityFeed(items) {
    var el = document.getElementById('activity-feed');
    if (!el) return;
    el.innerHTML = items.slice(0, 5).map(function (a) {
        return '<div class="activity-item small py-1">\uD83C\uDFB2 ' + escHtml(a.message) + '</div>';
    }).join('');
}

function addActivityItem(message) {
    var el = document.getElementById('activity-feed');
    if (!el) return;
    var item     = document.createElement('div');
    item.className   = 'activity-item small py-1';
    item.textContent = message;
    el.prepend(item);
    while (el.children.length > 5) el.lastChild.remove();
}

function showToast(msg) {
    var container = document.getElementById('toast-container');
    if (!container) return;
    var el     = document.createElement('div');
    el.className   = 'toast-msg alert alert-dark py-1 px-3 mb-1 small';
    el.textContent = msg;
    container.appendChild(el);
    setTimeout(function () { el.remove(); }, 3000);
}

function startCountdown(elementId, seconds, onDone) {
    var count = seconds;
    var el    = document.getElementById(elementId);
    var tick  = function () {
        if (el) el.textContent = count + 's';
        if (count-- <= 0) { if (onDone) onDone(); }
        else setTimeout(tick, 1000);
    };
    tick();
}

function escHtml(str) {
    return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

// ── Expose for Blade onclick= attributes ─────────────────────────────────────

window.handleBetClick = handleBetClick;
window.clearAllBets   = clearAllBets;
window.selectChip     = function (amount) {
    selectedChip = amount;
    document.querySelectorAll('.chip').forEach(function (c) {
        c.classList.toggle('active', parseInt(c.dataset.value) === amount);
    });
};
