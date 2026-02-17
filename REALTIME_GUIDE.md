# Lucky Puffin - Real-Time Multiplayer Implementation Guide

## 🎯 Overview

This guide explains how to add real-time multiplayer features to Lucky Puffin using AJAX polling. The database schema and API endpoints are already prepared. You just need to integrate the frontend polling system.

## ✅ What's Already Done

### Database Tables (in lucky_puffin_schema.sql)
- ✅ `active_sessions` - Tracks who's currently playing
- ✅ `activity_feed` - Records recent wins/losses/actions

### API Endpoints (in api.php)
- ✅ `startSession` - Called when player starts betting
- ✅ `updateTimer` - Syncs timer with server
- ✅ `updateSessionStatus` - Updates to betting/dealing/finished
- ✅ `endSession` - Marks game as complete
- ✅ `getGameState` - **Main polling endpoint** - returns everything
- ✅ `getActiveSessions` - Get who's playing
- ✅ `getRecentActivity` - Get activity feed

### Player Model Methods (in Player.php)
- ✅ All session management methods implemented
- ✅ Activity logging implemented
- ✅ Real-time state tracking implemented

## 🚀 Frontend Integration Steps

### Step 1: Add Real-Time UI Elements

Add these to your HTML (after the top bar):

```html
<!-- Who's Playing Indicator -->
<div class="whos-playing" id="whos-playing" style="background: rgba(0,0,0,0.7); color: #ffd700; padding: 5px 10px; border-radius: 6px; font-size: 0.75rem; margin-bottom: 5px; text-align: center; display: none;">
    <i class="bi bi-person-fill-check"></i> <span id="playing-player-name">Player</span> is playing...
</div>

<!-- Recent Activity Feed -->
<div class="activity-feed" id="activity-feed" style="background: rgba(0,0,0,0.6); border-radius: 6px; padding: 5px 8px; margin-bottom: 5px; max-height: 60px; overflow-y: auto;">
    <div class="activity-item" style="opacity: 0.5; color: #fff; font-size: 0.7rem;">Recent activity will appear here...</div>
</div>

<!-- Add LIVE indicator to top bar -->
<span class="live-indicator" style="background: #ff0000; color: #fff; padding: 2px 8px; border-radius: 10px; font-size: 0.7rem; font-weight: bold; animation: livePulse 2s infinite;">🔴 LIVE</span>
```

### Step 2: Add CSS for Real-Time Elements

```css
.live-indicator {
    animation: livePulse 2s infinite;
}

@keyframes livePulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.6; }
}

.whos-playing.active {
    display: block !important;
}

.leaderboard .card.playing {
    border-color: #00ff00;
    box-shadow: 0 0 15px rgba(0,255,0,0.5);
    animation: playingPulse 1.5s infinite;
}

@keyframes playingPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.activity-item.won {
    color: #00ff00;
}

.activity-item.lost {
    color: #ff6b6b;
}
```

### Step 3: Add Polling Variables

Add these to your JavaScript game state section:

```javascript
const POLLING_INTERVAL = 2000; // 2 seconds
let pollingInterval = null;
let sessionId = null;
let syncedTimer = false;
```

### Step 4: Implement Polling Functions

Add these JavaScript functions:

```javascript
// ============================================
// REAL-TIME POLLING
// ============================================

async function pollGameState() {
    try {
        const result = await apiCall('getGameState');
        
        if (result.data) {
            updateGameState(result.data);
        }
    } catch (error) {
        console.error('Polling error:', error);
    }
}

function updateGameState(gameState) {
    // Update leaderboard with playing indicators
    if (gameState.leaderboard) {
        updateLeaderboardWithState(gameState.leaderboard, gameState.active_sessions);
    }
    
    // Update activity feed
    if (gameState.recent_activity) {
        updateActivityFeed(gameState.recent_activity);
    }
    
    // Check active sessions
    if (gameState.active_sessions && gameState.active_sessions.length > 0) {
        const otherPlayersSessions = gameState.active_sessions.filter(s => s.player_id != playerId);
        
        if (otherPlayersSessions.length > 0) {
            const session = otherPlayersSessions[0];
            
            // Show who's playing
            document.getElementById('playing-player-name').textContent = session.player_name;
            document.getElementById('whos-playing').classList.add('active');
            
            // Sync timer if someone is betting
            if (session.session_status === 'betting' && !gameInProgress && !timerStarted) {
                syncTimerWithSession(session);
            }
        } else {
            document.getElementById('whos-playing').classList.remove('active');
            if (syncedTimer && !timerStarted) {
                stopTimer();
                showTimerWaiting();
                syncedTimer = false;
            }
        }
    } else {
        document.getElementById('whos-playing').classList.remove('active');
        if (syncedTimer && !timerStarted) {
            stopTimer();
            showTimerWaiting();
            syncedTimer = false;
        }
    }
}

function syncTimerWithSession(session) {
    if (session.timer_remaining > 0) {
        timeRemaining = session.timer_remaining;
        syncedTimer = true;
        
        const timerEl = document.getElementById('bet-timer');
        timerEl.classList.add('synced');
        timerEl.style.background = 'linear-gradient(135deg, #00c853, #00e676)';
        
        if (timerInterval) clearInterval(timerInterval);
        
        timerInterval = setInterval(() => {
            timeRemaining--;
            updateTimerDisplay();
            
            if (timeRemaining <= 0) {
                clearInterval(timerInterval);
                syncedTimer = false;
                timerEl.classList.remove('synced');
                timerEl.style.background = '';
                showTimerWaiting();
            }
        }, 1000);
        
        updateTimerDisplay();
    }
}

function updateActivityFeed(activities) {
    if (activities.length === 0) return;
    
    const activityFeed = document.getElementById('activity-feed');
    activityFeed.innerHTML = '';
    
    activities.slice(0, 3).forEach(activity => {
        const item = document.createElement('div');
        item.className = `activity-item ${activity.activity_type}`;
        
        let icon = '🎲';
        if (activity.activity_type === 'won') icon = '🎉';
        if (activity.activity_type === 'lost') icon = '😢';
        if (activity.activity_type === 'dealing') icon = '🃏';
        
        item.textContent = `${icon} ${activity.message}`;
        activityFeed.appendChild(item);
    });
}

function updateLeaderboardWithState(leaderboard, activeSessions) {
    const leaderboardEl = document.getElementById('leaderboard');
    
    if (!leaderboard || leaderboard.length === 0) {
        leaderboardEl.innerHTML = '<div class="text-white p-2" style="font-size: 0.75rem;">No players yet</div>';
        return;
    }
    
    const activePlayerIds = activeSessions ? activeSessions.map(s => s.player_id) : [];
    
    leaderboardEl.innerHTML = '';
    
    leaderboard.forEach((player, index) => {
        const isMe = player.id === playerId;
        const isPlaying = activePlayerIds.includes(player.id);
        const card = document.createElement('div');
        card.className = 'card text-center position-relative';
        if (isMe) card.style.borderColor = '#ffd700';
        if (isPlaying) card.classList.add('playing');
        
        card.innerHTML = `
            <div class="rank-badge">${index + 1}</div>
            <div class="p-2">
                ${isPlaying ? '<div style="color: #00ff00; font-size: 0.6rem;">▶ PLAYING</div>' : ''}
                <i class="bi bi-person-circle" style="font-size: 1.8rem;"></i>
                <p class="mb-0 fw-bold" style="font-size: 0.7rem;">${player.player_name}${isMe ? ' 👈' : ''}</p>
                <small class="text-success fw-bold" style="font-size: 0.7rem;">${player.balance}</small>
            </div>
        `;
        
        leaderboardEl.appendChild(card);
    });
}

function startPolling() {
    if (pollingInterval) return;
    
    pollingInterval = setInterval(pollGameState, POLLING_INTERVAL);
    pollGameState(); // Initial poll
}

function stopPolling() {
    if (pollingInterval) {
        clearInterval(pollingInterval);
        pollingInterval = null;
    }
}
```

### Step 5: Start Polling on Init

Update your `init()` function:

```javascript
async function init() {
    showLoading();
    
    try {
        // ... existing initialization code ...
        
        // Start real-time polling
        startPolling();
        
        console.log('Game initialized with real-time features');
        
    } catch (error) {
        // ... existing error handling ...
    } finally {
        hideLoading();
    }
}
```

### Step 6: Broadcast Session Start

Update `handleBetClick` to start a session:

```javascript
if (!timerStarted) {
    // Start session in database
    try {
        const result = await apiCall('startSession', {
            playerId: playerId,
            playerName: playerName
        });
        
        if (result.data) {
            sessionId = result.data.session_id;
        }
    } catch (error) {
        console.error('Failed to start session:', error);
    }
    
    startBettingTimer();
    timerStarted = true;
    // ... rest of code ...
}
```

### Step 7: Update Timer on Server

Modify `startBettingTimer` to sync:

```javascript
async function startBettingTimer() {
    timeRemaining = 20;
    updateTimerDisplay();
    
    if (timerInterval) clearInterval(timerInterval);
    
    timerInterval = setInterval(async () => {
        timeRemaining--;
        updateTimerDisplay();
        
        // Update server every 2 seconds
        if (timeRemaining % 2 === 0) {
            try {
                const totalBet = Object.values(currentBets).reduce((sum, bet) => sum + bet, 0);
                await apiCall('updateTimer', {
                    playerId: playerId,
                    timeRemaining: timeRemaining,
                    totalBet: totalBet
                });
            } catch (error) {
                console.error('Failed to update timer:', error);
            }
        }
        
        if (timeRemaining <= 0) {
            clearInterval(timerInterval);
            onTimerExpired();
        }
    }, 1000);
}
```

### Step 8: Update Session Status

Add to `handleDeal`:

```javascript
async function handleDeal() {
    // ... existing code ...
    
    // Update session status to dealing
    try {
        await apiCall('updateSessionStatus', {
            playerId: playerId,
            status: 'dealing'
        });
    } catch (error) {
        console.error('Failed to update session status:', error);
    }
    
    // ... rest of dealing code ...
}
```

### Step 9: End Session After Game

Update `recordGameResult`:

```javascript
async function recordGameResult(totalBet, totalWon, playerTotal, bankerTotal, result) {
    if (!playerId) return;
    
    try {
        await apiCall('recordGame', {
            // ... existing parameters ...
        });
        
        // End session
        const won = totalWon > 0;
        await apiCall('endSession', {
            playerId: playerId,
            won: won,
            amount: won ? totalWon : totalBet
        });
        
        await updateLeaderboard();
    } catch (error) {
        console.error('Failed to record game:', error);
    }
}
```

### Step 10: Clean Up on Exit

Update `handleExit`:

```javascript
async function handleExit() {
    if (confirm(`Save and exit? Your balance of ${playerBalance} WPUFF will be saved.`)) {
        stopPolling(); // Stop polling
        await savePlayerBalance();
        showToast(`Goodbye ${playerName}! Come back in 1 hour for bonus!`);
        setTimeout(() => location.reload(), 2000);
    }
}
```

## 🎮 How It Works

### Real-Time Flow:

1. **Player A starts betting**
   - Frontend calls `startSession` API
   - Database creates entry in `active_sessions`
   - Timer starts locally

2. **Every 2 seconds (all players)**
   - Frontend calls `getGameState` API
   - Receives: active_sessions, leaderboard, recent_activity

3. **Player B sees Player A playing**
   - UI shows "Player A is playing..."
   - Timer syncs with Player A's countdown
   - Leaderboard highlights Player A with green "▶ PLAYING"

4. **Player A deals cards**
   - Status changes to 'dealing'
   - Activity feed shows "Player A is dealing cards"

5. **Player A finishes game**
   - Session marked as 'finished'
   - Activity feed shows "Player A won 500 WPUFF!" 
   - Leaderboard updates immediately
   - All players see the update within 2 seconds

## 📊 Testing Real-Time Features

### Test Checklist:

1. **Open two browser windows** (different browsers or incognito)
2. **Window 1**: Start betting
   - ✅ Timer should start
   - ✅ Your name appears in activity feed
3. **Window 2**: Check after 2-3 seconds
   - ✅ Should see "Player1 is playing..."
   - ✅ Timer should be synced
   - ✅ Leaderboard shows Player1 with green glow
4. **Window 1**: Complete the game
   - ✅ Result appears
5. **Window 2**: Check after 2-3 seconds
   - ✅ Activity feed shows win/loss
   - ✅ Leaderboard updates
   - ✅ "Playing" indicator disappears

## ⚙️ Configuration

### Adjust Polling Rate:

```javascript
const POLLING_INTERVAL = 2000; // 2 seconds (recommended)
// For faster updates: 1000 (1 second) - more server load
// For slower updates: 3000 (3 seconds) - less real-time
```

### Cleanup Old Sessions:

Sessions auto-expire after 2 minutes of inactivity (set in `startSession`)

## 🐛 Troubleshooting

### "Who's playing" never shows
- Check browser console for API errors
- Verify database tables were created
- Test `getGameState` API endpoint directly

### Timer doesn't sync
- Check `updateTimer` API is being called
- Verify `active_sessions` table is being updated
- Make sure polling is started (`startPolling()`)

### Leaderboard doesn't update
- Check `getLeaderboard` is in `getGameState` response
- Verify `updateLeaderboardWithState` is being called
- Check polling interval is running

### Activity feed is empty
- Verify `activity_feed` table exists
- Check `logActivity` is being called in Player.php
- Test `getRecentActivity` API endpoint

## 🚀 Next Steps

After implementing:
1. Test with 2-3 browsers simultaneously
2. Check network tab for polling requests
3. Verify database tables are being updated
4. Monitor server load (2-second polling)
5. Consider WebSockets for even better real-time (future enhancement)

## 📝 Notes

- AJAX polling is simple and works everywhere
- 2-second interval is good balance between real-time and server load
- For 100+ concurrent players, consider WebSockets
- Sessions auto-cleanup after 2 minutes
- Activity feed keeps last 100 entries

---

**Ready to go LIVE!** 🐧🎲
