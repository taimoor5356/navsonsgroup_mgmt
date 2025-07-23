const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js')
   .sass('resources/sass/app.scss', 'public/css')
   .babel('public/js/app.js', 'public/js/app.babel.js')
   .version();