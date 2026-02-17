import 'bootstrap';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import axios from 'axios';

// ── Axios defaults ───────────────────────────────────────────────────────────
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['Accept'] = 'application/json';

// Attach CSRF token from meta tag (present in Blade layout)
const csrfToken = document.head.querySelector('meta[name="csrf-token"]');
if (csrfToken) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.content;
}

// Attach Sanctum Bearer token from localStorage (set after guestLogin)
const authToken = localStorage.getItem('auth_token');
if (authToken) {
    window.axios.defaults.headers.common['Authorization'] = `Bearer ${authToken}`;
}

// ── Laravel Echo (WebSocket client via Reverb) ───────────────────────────────
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster:      'reverb',
    key:              import.meta.env.VITE_REVERB_APP_KEY,
    wsHost:           import.meta.env.VITE_REVERB_HOST,
    wsPort:           import.meta.env.VITE_REVERB_PORT ?? 8080,
    wssPort:          import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS:         (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
    enabledTransports:['ws', 'wss'],
    // Auth for private channels (player.{id})
    authEndpoint:     '/broadcasting/auth',
});
