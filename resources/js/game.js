/**
 * Lucky Puffin — game.js
 * Handles WebSocket events, betting UI, and round lifecycle.
 * Replaces the old polling approach entirely.
 */

// ── State ────────────────────────────────────────────────────────────────────

let playerId    = null;
let playerName  = null;
let playerBalance = 0;
let authToken   = null;

let currentRound  = null;
let currentBets   = { player: 0, banker: 0, tie: 0, playerPair: 0, bankerPair: 0, randomPair: 0 };
let selectedChip  = 0;
let hasBetThisRound = false;

const API_BASE = '/api/v1';

// ── Bootstrap from Blade data attributes ────────────────────────────────────

function init() {
    const app = document.getElementById('game-app');
    playerId      = parseInt(app.dataset.playerId);
    playerName    = app.dataset.playerName;
    playerBalance = parseInt(app.dataset.balance);
    authToken     = localStorage.getItem('auth_token');

    // Update auth header for all future axios calls
    window.axios.defaults.headers.common['Authorization'] = `Bearer ${authToken}`;

    updateBalanceDisplay();
    subscribeToGameTable();
    subscribeToPlayerChannel();
    fetchInitialState();
}

// ── WebSocket Subscriptions ───────────────────────────────────────────────────

function subscribeToGameTable() {
    window.Echo.channel('game-table')

        .listen('.round.started', (data) => {
            console.log('[WS] round.started', data);
            currentRound = data;
            clearBets();          // Always clear on new round
            enableBetting();
            updateTimerDisplay(data.timerSeconds);
        })

        .listen('.timer.tick', (data) => {
            // Server pushes every second — smooth countdown, no polling
            updateTimerDisplay(data.secondsRemaining);
        })

        .listen('.bet.placed', (data) => {
            // Another player placed a bet — show in activity feed
            addActivity(`🎲 ${data.playerName} bet ${data.totalBet} WPUFF`);
            document.getElementById('player-count').textContent = data.activePlayers;
        })

        .listen('.cards.dealt', (data) => {
            console.log('[WS] cards.dealt', data);
            currentRound = data;
            disableBetting();
            showSharedCards(data);
        })

        .listen('.round.finished', (data) => {
            // Non-betting players: close overlay after dealing animation
            const maxCards    = Math.max(data.playerCards.length, data.bankerCards.length);
            const animTime    = (maxCards * 1000) + 1500;
            setTimeout(() => {
                document.getElementById('card-dealing-overlay').classList.remove('active');
            }, animTime + 2000);
        });
}

function subscribeToPlayerChannel() {
    if (!playerId) return;

    window.Echo.private(`player.${playerId}`)

        .listen('.round.result', (data) => {
            console.log('[WS] round.result', data);
            // Sync balance to server value (source of truth)
            playerBalance = data.newBalance;
            updateBalanceDisplay();
            showResult(data);
        });
}

// ── Initial State (REST fallback on page load) ───────────────────────────────

async function fetchInitialState() {
    try {
        const { data } = await window.axios.get(`${API_BASE}/game/state`);
        if (data.current_round) {
            currentRound = data.current_round;
            renderRoundState(data);
        }
        if (data.leaderboard)     renderLeaderboard(data.leaderboard, data.active_players);
        if (data.recent_activity) renderActivityFeed(data.recent_activity);
    } catch (err) {
        console.error('Failed to load initial state', err);
    }
}

// ── Betting ──────────────────────────────────────────────────────────────────

async function handleBetClick(box) {
    if (!currentRound) return showToast('Loading...');

    const status = currentRound.round_status;
    if (status !== 'waiting' && status !== 'betting') {
        return showToast('Wait for the next round!');
    }

    if (selectedChip === 0)           return showToast('Select a chip first!');

    const betType = box.dataset.betType;

    if (betType === 'player' && currentBets.banker  > 0) return showToast('Cannot bet Player and Banker!');
    if (betType === 'banker' && currentBets.player  > 0) return showToast('Cannot bet Player and Banker!');

    const newTotal = Object.values({ ...currentBets, [betType]: currentBets[betType] + selectedChip })
                           .reduce((s, v) => s + v, 0);

    if (newTotal > playerBalance) return showToast('Insufficient balance!');

    // Optimistic update
    currentBets[betType] += selectedChip;
    playerBalance        -= selectedChip;
    updateBetDisplay(betType);
    updateBalanceDisplay();

    try {
        await window.axios.post(`${API_BASE}/game/bet`, {
            round_id: currentRound.id,
            bets:     currentBets,
        });
        hasBetThisRound = true;
        showToast(`+${selectedChip} on ${betType.toUpperCase()}`);
    } catch (err) {
        // Rollback optimistic update
        currentBets[betType] -= selectedChip;
        playerBalance        += selectedChip;
        updateBetDisplay(betType);
        updateBalanceDisplay();
        showToast(err.response?.data?.message ?? 'Failed to place bet');
    }
}

async function clearAllBets() {
    if (!currentRound || currentRound.round_status !== 'betting') {
        return showToast('Cannot clear bets now!');
    }
    const total = Object.values(currentBets).reduce((s, v) => s + v, 0);
    if (total === 0) return;

    try {
        const { data } = await window.axios.post(`${API_BASE}/game/clear`, {
            round_id: currentRound.id,
        });
        playerBalance = data.balance;
        clearBets();
        updateBalanceDisplay();
        showToast(`Bets cleared! ${total} WPUFF refunded`);
    } catch (err) {
        showToast('Could not clear bets');
    }
}

// ── UI Helpers ───────────────────────────────────────────────────────────────

function clearBets() {
    Object.keys(currentBets).forEach(k => { currentBets[k] = 0; updateBetDisplay(k); });
    document.querySelectorAll('.betting-box').forEach(b => b.classList.remove('disabled'));
    hasBetThisRound = false;
}

function enableBetting() {
    document.getElementById('bet-timer').classList.remove('d-none');
    document.querySelectorAll('.betting-box').forEach(b => b.classList.remove('disabled'));
}

function disableBetting() {
    document.querySelectorAll('.betting-box').forEach(b => b.classList.add('disabled'));
}

function updateTimerDisplay(seconds) {
    const el = document.getElementById('bet-timer');
    if (!el) return;
    el.innerHTML = `<i class="bi bi-clock-fill"></i> Time: <span>${seconds}</span>s`;
    el.classList.toggle('text-danger', seconds <= 5);
}

function updateBetDisplay(betType) {
    const map = {
        player:     'player-total',
        banker:     'banker-total',
        tie:        'tie-total',
        playerPair: 'player-pair-total',
        bankerPair: 'banker-pair-total',
        randomPair: 'random-pair-total',
    };
    const el = document.getElementById(map[betType]);
    if (el) el.textContent = currentBets[betType];
}

function updateBalanceDisplay() {
    const el = document.getElementById('player-balance');
    if (el) el.textContent = playerBalance.toLocaleString();
}

function showSharedCards(data) {
    const overlay   = document.getElementById('card-dealing-overlay');
    const pHand     = document.getElementById('player-hand');
    const bHand     = document.getElementById('banker-hand');

    overlay.classList.add('active');
    pHand.innerHTML = '';
    bHand.innerHTML = '';

    document.getElementById('player-total-score').textContent = '?';
    document.getElementById('banker-total-score').textContent = '?';

    const DELAY = 1000; // 1 second per card

    data.playerCards.forEach((card, i) => {
        setTimeout(() => appendCard(pHand, card), i * DELAY);
    });
    data.bankerCards.forEach((card, i) => {
        setTimeout(() => appendCard(bHand, card), (i * DELAY) + 500);
    });

    const totalTime = Math.max(data.playerCards.length, data.bankerCards.length) * DELAY + 800;
    setTimeout(() => {
        document.getElementById('player-total-score').textContent = data.playerTotal;
        document.getElementById('banker-total-score').textContent = data.bankerTotal;
    }, totalTime);
}

function appendCard(container, card) {
    const el      = document.createElement('div');
    el.className  = 'card-placeholder';
    el.textContent = card.display;
    el.style.color = ['♥', '♦'].includes(card.suit) ? '#dc3545' : '#212529';
    container.appendChild(el);
}

function showResult(data) {
    const maxCards  = 3;
    const animTime  = (maxCards * 1000) + 1500;

    setTimeout(() => {
        document.getElementById('card-dealing-overlay').classList.remove('active');
        document.getElementById('result-overlay').classList.add('active');
        document.getElementById('result-title').textContent = data.result.replace('_', ' ');

        const profit = data.profit;
        document.getElementById('result-details').textContent =
            profit > 0 ? `+${profit} WPUFF 🎉` :
            profit < 0 ? `${profit} WPUFF 😢` :
                         'Bet returned (Tie)';

        startResultCountdown();
    }, animTime);
}

function startResultCountdown() {
    let count = 5;
    const el  = document.getElementById('result-countdown');

    const tick = () => {
        if (el) el.textContent = count + 's';
        if (count-- <= 0) {
            document.getElementById('result-overlay').classList.remove('active');
        } else {
            setTimeout(tick, 1000);
        }
    };
    tick();
}

function renderRoundState(data) {
    const round = data.current_round;
    if (!round) return;

    if (round.round_status === 'betting') {
        enableBetting();
        updateTimerDisplay(round.timer_remaining ?? 0);
    }
    if (round.player_cards?.length) {
        showSharedCards({
            playerCards:  round.player_cards,
            bankerCards:  round.banker_cards,
            playerTotal:  round.player_total,
            bankerTotal:  round.banker_total,
        });
    }
}

function renderLeaderboard(leaders, activePlayers) {
    const el      = document.getElementById('leaderboard');
    if (!el) return;
    const activeIds = (activePlayers ?? []).map(p => p.player_id ?? p.id);

    el.innerHTML = leaders.map((p, i) => `
        <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-secondary">
            <span class="fw-bold text-warning">#${i + 1}</span>
            <span class="${p.id === playerId ? 'text-warning' : 'text-white'}">
                ${p.player_name}${activeIds.includes(p.id) ? ' 🟢' : ''}
            </span>
            <span class="text-success fw-bold">${p.balance.toLocaleString()}</span>
        </div>
    `).join('');
}

function renderActivityFeed(items) {
    const el = document.getElementById('activity-feed');
    if (!el) return;
    el.innerHTML = items.slice(0, 5).map(a => `
        <div class="activity-item small py-1">${a.message}</div>
    `).join('');
}

function addActivity(message) {
    const el   = document.getElementById('activity-feed');
    if (!el) return;
    const item = document.createElement('div');
    item.className   = 'activity-item small py-1';
    item.textContent = message;
    el.prepend(item);
    // Keep max 5
    while (el.children.length > 5) el.lastChild.remove();
}

function showToast(msg) {
    const el = document.getElementById('toast-container');
    if (!el) { console.log(msg); return; }
    const toast      = document.createElement('div');
    toast.className  = 'toast-msg alert alert-dark py-1 px-3 mb-1 small';
    toast.textContent = msg;
    el.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// ── Expose globally for onclick attributes ───────────────────────────────────

window.handleBetClick  = handleBetClick;
window.clearAllBets    = clearAllBets;
window.selectChip      = (amount) => {
    selectedChip = amount;
    document.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
    document.querySelector(`.chip[data-value="${amount}"]`)?.classList.add('active');
};

// ── Boot ─────────────────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', init);
