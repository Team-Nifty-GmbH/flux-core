import {defineConfig} from 'vite';
import laravel from 'laravel-vite-plugin';
import path from "path";
import tailwindcss from "tailwindcss";
import autoprefixer from "autoprefixer";

export default defineConfig({
    base: "/flux-assets/",
    plugins: [
        laravel({
            input: [
                '.{{ relative_path }}/resources/css/app.css',
                '.{{ relative_path }}/resources/js/app.js',
                '.{{ relative_path }}/resources/js/apex-charts.js',
                '.{{ relative_path }}/resources/js/alpine.js',
                '.{{ relative_path }}/resources/js/sw.js',
                '.{{ relative_path }}/resources/js/web-push.js',
                './vendor/team-nifty-gmbh/tall-datatables/resources/js/tall-datatables.js',
                './vendor/team-nifty-gmbh/tall-calendar/resources/js/index.js',
                './vendor/team-nifty-gmbh/tall-calendar/resources/css/calendar.css',
                './vendor/wireui/wireui/ts/index.ts',
            ],
            refresh: false
        })
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'vendor/wireui/wireui/ts')
        },
    },
    css: {
        postcss: {
            plugins: [
                tailwindcss,
                autoprefixer,
            ],
        },
    }
});
