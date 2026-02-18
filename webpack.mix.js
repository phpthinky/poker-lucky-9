const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Lucky Puffin — webpack.mix.js
 |--------------------------------------------------------------------------
 |
 | IMPORTANT: app.js MUST be defined before game.js so the browser
 | receives app.js first. Mix preserves definition order when outputting
 | separate files.
 |
 | app.js  → creates window.Echo, sets window.echoReady = true
 | game.js → waits for 'echo:ready' event before subscribing
 |
 */

mix
    .js('resources/js/app.js', 'public/js')
    .js('resources/js/game.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css')
    .sourceMaps();

// Expose MIX_ env vars to JS (read from .env at build time)
// These are fallbacks — runtime values come from Blade meta tags
mix.webpackConfig({
    plugins: [
        new (require('webpack').DefinePlugin)({
            'process.env.MIX_REVERB_APP_KEY': JSON.stringify(process.env.MIX_REVERB_APP_KEY || 'lucky-puffin-key'),
            'process.env.MIX_REVERB_HOST':    JSON.stringify(process.env.MIX_REVERB_HOST    || 'localhost'),
            'process.env.MIX_REVERB_PORT':    JSON.stringify(process.env.MIX_REVERB_PORT    || '8080'),
            'process.env.MIX_REVERB_SCHEME':  JSON.stringify(process.env.MIX_REVERB_SCHEME  || 'http'),
        }),
    ],
});
