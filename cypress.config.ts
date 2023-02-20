import { defineConfig } from "cypress";

export default defineConfig({
    experimentalWebKitSupport: true,
    component: {
        devServer: {
            framework: "vue",
            bundler: "vite",
        },
    },

    e2e: {
        setupNodeEvents(on, config) {
            return require("./cypress/plugins/index.ts")(on, config);
        },
        baseUrl: "http://localhost/",
        supportFile: "cypress/support/index.js",
        chromeWebSecurity: false,
        experimentalSessionAndOrigin: true,
        responseTimeout: 100000,
    },
});
