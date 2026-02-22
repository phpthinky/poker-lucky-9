# Lucky Puffin - Lucky 9 Poker Game

A multiplayer Lucky 9 (Baccarat-style) poker game with database connectivity, leaderboards, and hourly bonus system.

## 🎮 Features

- **Multiplayer Support**: All players share a global leaderboard
- **Guest System**: Automatic unique guest ID generation
- **Persistent Data**: MySQL database stores all player data
- **Hourly Bonuses**: Players get 10 chips per hour away (max 50 chips)
- **Real-time Leaderboard**: Top 5 players displayed
- **Game History**: All games are recorded in the database
- **Responsive Design**: Works on mobile and desktop

## 📋 Requirements

- PHP 8.2+
- MySQL 8.0+
- Web server (Apache/Nginx)
- Hostinger or any PHP hosting

## 🚀 Installation

### Step 1: Upload Files

Upload all files to your Hostinger public_html directory:
```
/public_html/
  ├── index.html          (Main game file)
  ├── api.php             (API endpoint)
  ├── config.php          (Database configuration)
  ├── Database.php        (Database class)
  ├── Player.php          (Player model)
  └── .htaccess           (Optional: for clean URLs)
```

### Step 2: Create MySQL Database

1. Log in to your Hostinger cPanel
2. Go to MySQL Databases
3. Create a new database called `lucky_puffin`
4. Create a database user with a strong password
5. Assign the user to the database with ALL PRIVILEGES
6. Note down:
   - Database name
   - Database username
   - Database password
   - Database host (usually `localhost`)

### Step 3: Import Database Schema

1. Go to phpMyAdmin in cPanel
2. Select your `lucky_puffin` database
3. Click on "Import" tab
4. Upload the `lucky_puffin_schema.sql` file
5. Click "Go" to execute

Alternatively, run the SQL commands directly in phpMyAdmin SQL tab.

### Step 4: Configure Database Connection

Edit `config.php` and update these values:

```php
define('DB_HOST', 'localhost');          // Your database host
define('DB_NAME', 'lucky_puffin');       // Your database name
define('DB_USER', 'your_username');      // Your database username
define('DB_PASS', 'your_password');      // Your database password
```

### Step 5: Set Permissions

Ensure proper file permissions:
```bash
chmod 644 *.php
chmod 644 *.html
chmod 755 public_html
```

### Step 6: Test the Installation

1. Visit your domain: `https://worldpuff.com`
2. The game should load and create a guest account
3. Check if balance updates are saved
4. Test the leaderboard functionality

## 🔧 Configuration

### API URL Configuration

In `index.html`, update the API URL if needed (line ~530):
```javascript
const API_URL = 'api.php'; // Change to 'https://worldpuff.com/api.php' if needed
```

### CORS Settings (if needed)

If hosting the frontend and backend on different domains, update CORS in `config.php`:
```php
header('Access-Control-Allow-Origin: https://your-frontend-domain.com');
```

### Production Settings

For production, update `config.php`:
```php
// Disable error display
ini_set('display_errors', 0);
error_reporting(0);

// Enable error logging
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/php-error.log');
```

## 📊 Database Tables

### players
- Stores player information, balance, and stats
- Auto-generates unique guest IDs
- Tracks last visit for hourly bonuses

### game_history
- Records every game played
- Stores bets, results, and winnings
- Useful for analytics

### bonus_claims
- Tracks hourly bonus claims
- Prevents duplicate bonuses

## 🎲 Game Rules

- **Objective**: Get closest to 9
- **Card Values**: 
  - Ace = 1
  - 2-9 = Face value
  - 10, J, Q, K = 0
- **Payouts**:
  - Player/Banker: 1:1
  - Tie: 8:1
  - Pairs: 11:1
- **Special Rules**: Cannot bet on both Player AND Banker

## 🛠️ Troubleshooting

### "Database connection failed"
- Check your database credentials in `config.php`
- Ensure the database user has proper permissions
- Verify the database exists

### "API request failed"
- Check PHP error logs
- Ensure all PHP files are uploaded
- Verify file permissions

### Leaderboard not updating
- Check if `game_history` table is being populated
- Ensure database user has INSERT/UPDATE permissions
- Check browser console for JavaScript errors

### Balance not saving
- Verify API calls are successful (check Network tab in browser)
- Ensure `players` table is writable
- Check PHP error logs

## 🔒 Security Recommendations

1. **Use HTTPS**: Always use SSL certificate
2. **Strong Passwords**: Use strong database passwords
3. **Input Validation**: API validates all inputs
4. **SQL Injection Protection**: Uses PDO prepared statements
5. **Rate Limiting**: Consider adding rate limiting to API
6. **Backup**: Regular database backups

## 📈 Future Enhancements

- [ ] User authentication (login/register)
- [ ] Chat system
- [ ] Tournament mode
- [ ] Achievements system
- [ ] Mobile app version
- [ ] Admin dashboard
- [ ] Real-time multiplayer (WebSockets)
- [ ] Payment integration for chips

## 🐛 Known Issues

- None currently reported

## 📞 Support

For issues or questions:
- Check the troubleshooting section
- Review PHP error logs
- Check browser console for JavaScript errors

## 📄 License

All rights reserved. © 2026 Lucky Puffin / WorldPuff

## 🙏 Credits

Built for worldpuff.com using:
- Bootstrap 5.3
- Bootstrap Icons
- PHP 8.2
- MySQL 8.0
