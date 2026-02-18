/**
 * app.js — Bootstrap + Echo + Axios
 *
 * Hostinger shared hosting uses PUSHER (cloud WebSocket).
 * Reverb requires a persistent process that shared hosting cannot run.
 */

import 'bootstrap';
import axios    from 'axios';
import Echo     from 'laravel-echo';
import Pusher   from 'pusher-js';

// ── Axios defaults ────────────────────────────────────────────────────────────
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['Accept']           = 'application/json';

const csrf = document.head.querySelector('meta[name="csrf-token"]');
if (csrf) window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrf.content;

const token = localStorage.getItem('auth_token');
if (token) window.axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;

// ── Laravel Echo via Pusher ───────────────────────────────────────────────────
// Pusher key/cluster come from meta tags set by Blade (read from .env at runtime)
// This avoids needing to rebuild JS when credentials change.
window.Pusher = Pusher;

const pusherKey     = document.querySelector('meta[name="pusher-key"]')?.content
                   || process.env.MIX_PUSHER_APP_KEY
                   || '';

const pusherCluster = document.querySelector('meta[name="pusher-cluster"]')?.content
                   || process.env.MIX_PUSHER_APP_CLUSTER
                   || 'ap1';

if (!pusherKey) {
    console.error('[App] PUSHER key is empty! Check .env PUSHER_APP_KEY and re-run npm run prod');
}

window.Echo = new Echo({
    broadcaster:  'pusher',
    key:          pusherKey,
    cluster:      pusherCluster,
    forceTLS:     true,                 // Always wss:// — required on Hostinger
    authEndpoint: '/broadcasting/auth', // Private channel auth
    auth: {
        headers: {
            Authorization: localStorage.getItem('auth_token')
                ? `Bearer ${localStorage.getItem('auth_token')}`
                : '',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
        },
    },
});

console.log('[App] Echo (Pusher) ready —', pusherCluster, '— key:', pusherKey.substring(0, 6) + '...');

// Signal game.js it can now subscribe to channels
window.echoReady = true;
document.dispatchEvent(new CustomEvent('echo:ready'));
