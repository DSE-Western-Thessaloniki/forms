const mix = require('laravel-mix');
require('laravel-mix-purgecss');

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

 mix.webpackConfig({
    output:{
        chunkFilename:'js/vuejs_code_split/[name].js',
    }
});

mix.js('resources/js/app.js', 'public/js')
   .sass('resources/sass/app.scss', 'public/css')
   .vue({ version: 2 })
   .extract()
   .version('js/vuejs_code_split/*.js')
   .purgeCss();

mix.setResourceRoot('/'+process.env.MIX_APP_DIR+'/')

if (!mix.inProduction()) {
    mix.sourceMaps();
       //.browserSync('forms.test');
}

