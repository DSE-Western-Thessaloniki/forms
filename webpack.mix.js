const mix = require("laravel-mix");
require("laravel-mix-purgecss");

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

// Εύρεση resource path από το APP_URL
var resources_dir;
var index = 0;
index = process.env.APP_URL.indexOf("//");
if (index === -1) {
    console.log("Σφάλμα με τον ορισμό του APP_URL στο .env!");
    process.exit(1);
}
index = process.env.APP_URL.indexOf("/", index + 2);
if (index === -1) {
    resources_dir = "/";
} else {
    resources_dir = process.env.APP_URL.substring(index);
    if (!resources_dir.endsWith("/")) {
        resources_dir += "/";
    }
}

mix.webpackConfig({
    output: {
        chunkFilename: "js/vuejs_code_split/[name].js",
        publicPath: resources_dir,
    },
});

mix.ts("resources/ts/app.ts", "public/js")
    .sass("resources/sass/app.scss", "public/css")
    .vue({ version: 3 })
    .extract()
    .purgeCss();

if (process.env.VERSIONING === "true") {
    mix.version("js/vuejs_code_split/*.js");
}

mix.setResourceRoot(resources_dir);

if (!mix.inProduction()) {
    mix.sourceMaps();
    //.browserSync('forms.test');
}