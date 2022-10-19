import { defineConfig } from "cypress";

import webpackConfig from "laravel-mix/setup/webpack.config";

export default defineConfig({
    component: {
        devServer: {
            framework: "vue",
            bundler: "webpack",
            webpackConfig: webpackConfig,
        },
    },
});
