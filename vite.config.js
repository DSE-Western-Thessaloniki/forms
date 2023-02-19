import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import path from "path";

// Εύρεση resource path από το APP_URL
// var resources_dir;
// var index = 0;
// index = process.env.APP_URL.indexOf("//");
// if (index === -1) {
//     console.log("Σφάλμα με τον ορισμό του APP_URL στο .env!");
//     process.exit(1);
// }
// index = process.env.APP_URL.indexOf("/", index + 2);
// if (index === -1) {
//     resources_dir = "/";
// } else {
//     resources_dir = process.env.APP_URL.substring(index);
//     if (!resources_dir.endsWith("/")) {
//         resources_dir += "/";
//     }
// }

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/sass/app.scss", "resources/ts/app.ts"],
            refresh: true,
        }),
        {
            name: "blade",
            handleHotUpdate({ file, server }) {
                if (file.endsWith(".blade.php")) {
                    server.ws.send({
                        type: "full-reload",
                        path: "*",
                    });
                }
            },
        },
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            "@": path.resolve(__dirname, "resources/ts"),
            "~bootstrap": path.resolve(__dirname, "node_modules/bootstrap"),
            "~@fortawesome": path.resolve(
                __dirname,
                "node_modules/@fortawesome"
            ),
            vue: path.resolve(
                __dirname,
                "node_modules/vue/dist/vue.esm-bundler.js"
            ),
        },
    },
    build: {
        outdir: "public",
    }
});
