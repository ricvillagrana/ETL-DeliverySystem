let mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js([
    'resources/assets/js/app.js',
    'resources/assets/js/editable.js',
    'node_modules/chart.js/src/chart.js',
    'node_modules/jquery/src/jquery.js',
    'node_modules/sweetalert2/dist/sweetalert2.all.min.js',
    'node_modules/popper.js/dist/popper.js',
    'node_modules/bootstrap/dist/js/bootstrap.js',
    'node_modules/code-prettify/src/prettify.js',
    'resources/assets/js/freeze_header.js',
], 'public/js').sass('resources/assets/sass/app.scss', 'public/css');
