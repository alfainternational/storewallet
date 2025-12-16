const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
    .vue()
    .sass('resources/sass/app.scss', 'public/css')
    .sass('resources/sass/rtl.scss', 'public/css')
    .extract(['vue', 'vue-router', 'vuex', 'axios'])
    .sourceMaps()
    .version();

// Admin Dashboard
mix.js('resources/js/admin.js', 'public/js')
    .vue()
    .sass('resources/sass/admin.scss', 'public/css');

// Merchant Dashboard
mix.js('resources/js/merchant.js', 'public/js')
    .vue()
    .sass('resources/sass/merchant.scss', 'public/css');

if (mix.inProduction()) {
    mix.version();
} else {
    mix.sourceMaps();
}

// Options
mix.options({
    processCssUrls: false,
    postCss: [
        require('autoprefixer')
    ]
});

// Copy Font Awesome fonts
mix.copyDirectory('node_modules/@fortawesome/fontawesome-free/webfonts', 'public/webfonts');
