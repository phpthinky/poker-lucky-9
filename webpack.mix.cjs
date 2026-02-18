const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js')
   .js('resources/js/game.js', 'public/js')  // Add game.js as separate entry
   .sass('resources/sass/app.scss', 'public/css')
   .sourceMaps(); // Optional