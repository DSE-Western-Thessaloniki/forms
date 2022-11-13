import { defineConfig } from "cypress";
import webpackConfig from "laravel-mix/setup/webpack.config";

export default defineConfig({
    experimentalWebKitSupport: true,
    component: {
        devServer: {
            framework: "vue",
            bundler: "webpack",
            webpackConfig,
        },
    },

    e2e: {
        setupNodeEvents(on, config) {
            return require("./cypress/plugins/index.js")(on, config);
        },
        baseUrl: "http://localhost/",
        supportFile: "cypress/support/index.js",
        chromeWebSecurity: false,
        experimentalSessionAndOrigin: true,
        responseTimeout: 100000,
    },
});
