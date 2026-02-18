# 🐧 Lucky Puffin — Hostinger Deploy Guide

## Why Each Error Happened

| Error | Cause | Fix |
|-------|-------|-----|
| `Echo initialized on https://localhost:443` | `.env` had `REVERB_HOST=localhost`, never updated | Set proper `.env` with Pusher keys |
| `wss://localhost/app/lucky-puffin-key failed` | Still using Reverb broadcaster, Reverb can't run on shared | Switch to Pusher in `.env` |
| `/api/v1/game/state 404` | `APP_URL=http://localhost` in `.env` + missing `.htaccess` routing | Fix `APP_URL` + deploy `.htaccess` |

---

## Step 1 — Folder Structure on Hostinger

Hostinger's document root is `public_html/`.
Your Laravel app must sit **outside** it for security:

```
/home/u123456789/
├── public_html/          ← web root (yourdomain.com points here)
│   └── .htaccess         ← routes traffic into laravel/public/
│
└── laravel/              ← Laravel app (NOT inside public_html!)
    ├── app/
    ├── config/
    ├── public/           ← Laravel's public dir
    │   ├── index.php
    │   ├── .htaccess
    │   ├── js/
    │   └── css/
    ├── .env
    └── ...
```

> ⚠️ If you put the whole project inside `public_html/`, your `.env` and
> source code are publicly accessible. Move Laravel one level up.

---

## Step 2 — Get Free Pusher Keys

1. Go to **https://pusher.com** → Sign up free
2. Dashboard → **Create App**
3. Fill in: App Name = `lucky-puffin`, Cluster = pick closest to your users
4. Go to **App Keys** tab — copy:
   - `app_id`
   - `key`
   - `secret`
   - `cluster` (e.g. `ap1`, `eu`, `mt1`)

Free tier limits: **100 connections, 200,000 messages/day** — fine for testing.

---

## Step 3 — Update .env

Edit `/home/u123456789/laravel/.env`:

```env
APP_NAME="Lucky Puffin"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com          # ← YOUR domain, no trailing slash

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=u123456789_luckypuffin      # ← Hostinger DB name
DB_USERNAME=u123456789_user             # ← Hostinger DB user
DB_PASSWORD=your_password               # ← Hostinger DB password

CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database

BROADCAST_CONNECTION=pusher

PUSHER_APP_ID=your_app_id               # ← from Pusher dashboard
PUSHER_APP_KEY=your_pusher_key          # ← from Pusher dashboard
PUSHER_APP_SECRET=your_pusher_secret    # ← from Pusher dashboard
PUSHER_APP_CLUSTER=ap1                  # ← your cluster

MIX_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
MIX_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

---

## Step 4 — Install Pusher PHP SDK

SSH into Hostinger (or use terminal in hPanel):

```bash
cd ~/laravel
composer require pusher/pusher-php-server
```

---

## Step 5 — Deploy .htaccess Files

**File 1** → Upload `public_html.htaccess` as `/home/u123456789/public_html/.htaccess`

```apache
Options -MultiViews -Indexes
RewriteEngine On

# Force HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [R=301,L]

# Serve real files/dirs from laravel/public/ directly
RewriteCond %{DOCUMENT_ROOT}/../laravel/public%{REQUEST_URI} -f
RewriteRule ^ /laravel/public%{REQUEST_URI} [L]

RewriteCond %{DOCUMENT_ROOT}/../laravel/public%{REQUEST_URI} -d
RewriteRule ^ /laravel/public%{REQUEST_URI} [L]

# Everything else → Laravel front controller
RewriteRule ^ /laravel/public/index.php [L]
```

**File 2** → The standard Laravel `/home/u123456789/laravel/public/.htaccess`
(already in your repo — just make sure it's there)

---

## Step 6 — Build JS with Pusher Keys

On your **local machine** (or Hostinger SSH if Node is available):

```bash
cd ~/laravel
npm install pusher-js laravel-echo      # if not already installed
npm run prod                            # builds with MIX_PUSHER_APP_KEY baked in
```

Then upload the compiled `public/js/app.js` and `public/js/game.js` to Hostinger.

---

## Step 7 — Run Migrations

Via Hostinger SSH terminal:

```bash
cd ~/laravel
php artisan migrate --force
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

---

## Step 8 — Set Up Cron for Game Timer

Hostinger hPanel → **Cron Jobs** → Add:

```
* * * * *   php /home/u123456789/laravel/artisan schedule:run >> /dev/null 2>&1
```

Then in `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule): void
{
    $schedule->command('game:tick')->everyMinute();
}
```

> **Note:** Cron fires every minute. The `game:tick` command reads `started_at`
> timestamp from DB and advances state based on elapsed time.
> Players see real-time updates via Pusher WebSocket.

---

## Step 9 — Create First Round

```bash
cd ~/laravel
php artisan game:start
```

---

## Step 10 — Verify It Works

Open browser console on your domain. You should see:

```
✅ [App] Echo (Pusher) ready — ap1 — key: abc123...
✅ [Game] Echo ready — subscribing to channels
✅ [Init] Game state loaded
```

Instead of:
```
❌ Echo initialized on https://localhost:443
❌ wss://localhost/app/lucky-puffin-key failed
❌ /api/v1/game/state 404
```

---

## Troubleshooting

### Still getting 404 on API routes?

```bash
# SSH into Hostinger
cd ~/laravel
php artisan route:list | grep "api/v1"
```

If routes show but 404 in browser → `.htaccess` not routing correctly.
Check `public_html/.htaccess` path points to the right laravel folder.

### Pusher not connecting?

Open browser console → Network → WS tab.
Should see: `wss://ws-ap1.pusher.com/app/yourkey?...`
NOT: `wss://localhost/app/...`

If still localhost → run `php artisan config:clear` on server,
then hard refresh browser (Ctrl+Shift+R).

### `mix()` throwing ManifestNotFoundException?

The compiled `public/mix-manifest.json` is missing.
Run `npm run prod` locally and upload:
- `public/js/app.js`
- `public/js/game.js`
- `public/css/app.css`
- `public/mix-manifest.json`

### Broadcasting not working (bets not showing for other players)?

```bash
# Check Pusher PHP SDK installed
composer show pusher/pusher-php-server

# Check config
php artisan tinker
>>> config('broadcasting.default')    # should return "pusher"
>>> config('broadcasting.connections.pusher.key')  # should return your key
```
