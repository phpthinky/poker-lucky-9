# Fix: `window.Echo` undefined in game.js

## The Error
```
game.js:45 Uncaught TypeError: Cannot read properties of undefined (reading 'channel')
    at subscribeToGameTable (game.js:45)
```

## Root Cause

With **Webpack Mix**, each `.js()` call produces a **separate, isolated bundle**.
Unlike Vite's module graph, these bundles don't share imports.

```
app.js  → compiled bundle 1  (creates window.Echo)
game.js → compiled bundle 2  (tries to use window.Echo)
```

The browser loads them as two separate `<script>` tags.
Even though `app.js` comes first in the HTML, by the time
`game.js` runs its `DOMContentLoaded` handler, `window.Echo`
might not be assigned yet — especially if `pusher-js` or the
Echo constructor takes a moment to initialize.

## The Fix (3 layers)

### Layer 1 — Custom Event Signal (app.js)
`app.js` dispatches `echo:ready` after Echo is fully constructed:

```javascript
window.Echo = new Echo({ ... });

window.echoReady = true;
document.dispatchEvent(new CustomEvent('echo:ready'));
```

### Layer 2 — Event Listener (game.js)
`game.js` listens for the signal before calling `.channel()`:

```javascript
document.addEventListener('DOMContentLoaded', () => {
    if (window.echoReady && window.Echo) {
        boot();  // Already ready
    } else {
        document.addEventListener('echo:ready', boot, { once: true });
        // + polling fallback below
    }
});
```

### Layer 3 — Polling Fallback (game.js)
In case the scripts load in unexpected order (e.g. CDN delay,
browser cache quirk), a 100ms poll catches it:

```javascript
let attempts = 0;
const poll = setInterval(() => {
    if (window.Echo) {
        clearInterval(poll);
        boot();
    } else if (++attempts > 50) {        // Give up after 5 seconds
        clearInterval(poll);
        setTimerLabel('WebSocket error — please refresh');
    }
}, 100);
```

---

## Script Load Order in Blade (CRITICAL)

```html
{{-- app.js MUST come before game.js --}}
<script src="{{ mix('js/app.js') }}"></script>   ← Creates window.Echo
<script src="{{ mix('js/game.js') }}"></script>  ← Waits for echo:ready
```

Do NOT use `defer` or `async` on these — it breaks the ordering guarantee.

---

## webpack.mix.js Order (CRITICAL)

```javascript
mix
    .js('resources/js/app.js', 'public/js')   // ← FIRST
    .js('resources/js/game.js', 'public/js')  // ← SECOND
    .sass('resources/sass/app.scss', 'public/css');
```

---

## Reverb Config: Two Ways

### Option A: Blade meta tags (recommended — runtime, no rebuild needed)
`index.blade.php`:
```html
<meta name="reverb-key"    content="{{ config('broadcasting.connections.reverb.key') }}">
<meta name="reverb-host"   content="{{ config('broadcasting.connections.reverb.options.host') }}">
<meta name="reverb-port"   content="{{ config('broadcasting.connections.reverb.options.port') }}">
<meta name="reverb-scheme" content="{{ config('broadcasting.connections.reverb.options.scheme') }}">
```
`app.js` reads these at runtime — no rebuild needed when you change `.env`.

### Option B: MIX_ env vars (baked in at build time)
`.env`:
```env
MIX_REVERB_APP_KEY="${REVERB_APP_KEY}"
MIX_REVERB_HOST="${REVERB_HOST}"
MIX_REVERB_PORT="${REVERB_PORT}"
MIX_REVERB_SCHEME="${REVERB_SCHEME}"
```
`app.js` reads `process.env.MIX_REVERB_*` as fallback if meta tags are missing.
Requires `npm run dev` after changing `.env`.

---

## Quick Debug Checklist

If `window.Echo` is still undefined:

1. **Open browser console** — look for `[App] Echo initialized on ...`
   - If missing: `app.js` didn't run or threw an error

2. **Check script order in page source** (`Ctrl+U`):
   ```html
   <script src="/js/app.js"></script>   ← Must be first
   <script src="/js/game.js"></script>
   ```

3. **Check meta tags**:
   ```html
   <meta name="reverb-key" content="lucky-puffin-key">
   ```
   - If `content` is empty: check `config/broadcasting.php` Reverb section

4. **Check Reverb is running**:
   ```bash
   php artisan reverb:start --port=8080 --debug
   ```
   Open browser console → Network tab → WS → should see handshake

5. **Check .env**:
   ```env
   BROADCAST_CONNECTION=reverb
   REVERB_APP_KEY=lucky-puffin-key
   REVERB_HOST=localhost
   REVERB_PORT=8080
   ```

6. **Clear config cache**:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```
