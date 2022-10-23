import { defineConfig } from "cypress";
import webpackConfig from "laravel-mix/setup/webpack.config";

export default defineConfig({
  component: {
    devServer: {
      framework: "vue",
      bundler: "webpack",
      webpackConfig,
    },
  },

  e2e: {
    setupNodeEvents(on, config) {
      // implement node event listeners here
    },
  },
});
