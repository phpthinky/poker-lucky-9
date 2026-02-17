# Lucky 9 Poker - Multiplayer Browser Game 🎰

A fully-featured Lucky 9 Poker game with multiplayer support, automatic chip rewards, and leaderboards!

## Features ✨

- 🎮 **Full Poker Gameplay** - Lucky 9 rules with Player, Banker, Tie, and Pair bets
- 👥 **Multiplayer Support** - Play with friends on different devices
- 💾 **Persistent Storage** - Your balance and progress are saved
- 🎁 **Hourly Rewards** - Get 10 bonus chips for every hour you're away (max 50)
- 🏆 **Leaderboard** - Top 5 players ranked by balance
- ⏰ **20-Second Timer** - Fast-paced betting rounds
- 🤖 **Bot Players** - Simulated players for solo mode
- 📱 **Responsive Design** - Works on desktop, tablet, and mobile
- 🚫 **Betting Rules** - Cannot bet on both Player AND Banker (realistic casino rules)

## Quick Start (Solo/Local Play) 🚀

### Option 1: Just Open the HTML File
1. Download `lucky9-poker.html`
2. Double-click to open in your browser
3. Start playing immediately!

Each browser/device gets a unique Guest ID and saved progress.

### Option 2: Open on Multiple Devices
1. Copy `lucky9-poker.html` to all your devices (phone, tablet, computer)
2. Open on each device - each gets its own player profile
3. All players appear in the shared leaderboard (stored locally on each device)

## True Multiplayer Setup (Server Mode) 🌐

For REAL multiplayer where all players see each other live:

### Prerequisites
- Node.js installed (download from [nodejs.org](https://nodejs.org))
- All files in the same folder

### Installation Steps

1. **Install Dependencies**
   ```bash
   npm install
   ```

2. **Start the Server**
   ```bash
   npm start
   ```
   
   Or for development with auto-restart:
   ```bash
   npm run dev
   ```

3. **Open in Browser**
   - Open `http://localhost:3000` on your computer
   - Share `http://YOUR_IP_ADDRESS:3000` with friends on same network

### Find Your IP Address
- **Windows**: Open Command Prompt, type `ipconfig`, look for IPv4 Address
- **Mac/Linux**: Open Terminal, type `ifconfig` or `ip addr`, look for inet

### Server Features
- Saves all player data to `players.json`
- Auto-saves every 30 seconds
- Keeps last 10 rounds in memory
- RESTful API for player management

## API Endpoints (Server Mode) 📡

### Get All Players
```
GET /api/players
```

### Get Specific Player
```
GET /api/player/:id
```

### Create/Update Player
```
POST /api/player
Body: { id, name, balance, lastVisit }
```

### Get Current Round
```
GET /api/round/current
```

### Join Round
```
POST /api/round/join
Body: { playerId, playerName, balance }
```

### Place Bet
```
POST /api/round/bet
Body: { playerId, bets: {player, banker, tie, pair} }
```

### Start New Round
```
POST /api/round/new
```

### Health Check
```
GET /api/health
```

## Game Rules 📜

### Lucky 9 Basics
- Closest to 9 wins
- Face cards (J, Q, K) = 0
- Aces = 1
- Number cards = face value
- If sum > 9, use last digit (e.g., 15 = 5)

### Betting Options
- **Player**: Pays 1:1 if Player hand wins
- **Banker**: Pays 1:1 if Banker hand wins
- **Tie**: Pays 8:1 if both hands tie
- **Pair**: Pays 11:1 if first two cards of any hand form a pair

### Important Rules
- ⚠️ **Cannot bet on both Player AND Banker** - Choose one side!
- ⏰ You have 20 seconds to place bets each round
- 🎁 Visit every hour for bonus chips (10 per hour, max 50)
- 💰 Start with $1,000 chips

## File Structure 📁

```
lucky9-poker/
├── lucky9-poker.html    # Main game file (works standalone)
├── lucky9-server.js     # Multiplayer server (optional)
├── package.json         # Node.js dependencies
├── players.json         # Player data (created automatically)
└── README.md           # This file
```

## Data Storage 💾

### Local Mode (Solo Play)
- Uses browser localStorage
- Data stays on your device
- Each browser/device has separate storage
- Export data via "Export Player Data" button

### Server Mode (Multiplayer)
- Stores player data in `players.json`
- Shared across all connected players
- Auto-saves every 30 seconds
- Survives server restarts

## Hourly Chip Rewards System 🎁

- Every hour you're away, earn 10 bonus chips
- Maximum 5 hours (50 chips total)
- Resets only when you visit again
- Encourages daily play without requiring constant attention

## Customization Options 🎨

Want to modify the game? Here are some easy tweaks:

### Change Starting Balance
Find this line in the HTML file:
```javascript
playerBalance = 1000;  // Change to any amount
```

### Adjust Timer Duration
```javascript
timeRemaining = 20;  // Change to seconds you want
```

### Modify Hourly Rewards
```javascript
const bonusChips = hoursToReward * 10;  // Change 10 to your reward amount
```

### Change Payout Odds
Find these lines:
```javascript
const pairWin = currentBets.pair * 11;   // Pair pays 11:1
const tieWin = currentBets.tie * 8;      // Tie pays 8:1
const playerWin = currentBets.player * 2; // Player pays 1:1 (x2 returns bet+win)
```

## Troubleshooting 🔧

### "Cannot find module" error
```bash
npm install
```

### Server won't start
- Check if port 3000 is already in use
- Try changing PORT in lucky9-server.js

### Players can't connect
- Make sure all devices are on same WiFi network
- Check firewall settings
- Use your actual IP address, not localhost

### Data not saving
- Check write permissions in folder
- Server mode: Look for players.json file
- Local mode: Check browser's localStorage (F12 → Application → Local Storage)

### Timer not working
- Hard refresh the page (Ctrl+Shift+R or Cmd+Shift+R)
- Clear browser cache

## Browser Support 🌐

- Chrome (recommended)
- Firefox
- Safari
- Edge
- Mobile browsers (iOS Safari, Chrome Mobile)

## Advanced: Deploy to Internet 🚀

Want to play with friends anywhere? Deploy the server to:

### Free Options
- **Heroku** - Free tier available
- **Glitch** - Easy deployment, free
- **Replit** - Run directly in browser
- **Railway** - Simple deployment

### Paid Options (Better Performance)
- **DigitalOcean** - $5/month
- **AWS** - Free tier available
- **Google Cloud** - Free tier available

## Future Features (TODO) 📝

- [ ] WebSocket integration for real-time updates
- [ ] Private rooms with room codes
- [ ] Chat system
- [ ] Player avatars
- [ ] Tournament mode
- [ ] Achievement system
- [ ] Sound effects
- [ ] Animation improvements
- [ ] Mobile app version

## Credits 👏

Created with ❤️ for poker enthusiasts!

## License 📄

MIT License - Feel free to modify and share!

## Support 💬

Having issues? Found a bug? Want to contribute?
- Check the console (F12) for error messages
- Export your player data before reporting issues
- Include browser and OS information

---

**Enjoy the game! 🎰 Good luck! 🍀**
