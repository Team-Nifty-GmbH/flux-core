import {defineConfig} from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    base: "/flux-assets/",
    plugins: [
        laravel({
            input: [
                './vendor/team-nifty-gmbh/flux-erp/resources/css/app.css',
                './vendor/team-nifty-gmbh/flux-erp/resources/js/app.js',
                './vendor/team-nifty-gmbh/flux-erp/resources/js/apex-charts.js',
                './vendor/team-nifty-gmbh/flux-erp/resources/js/alpine.js',
                './vendor/team-nifty-gmbh/flux-erp/resources/js/sw.js',
                './vendor/team-nifty-gmbh/flux-erp/resources/js/web-push.js',
                './vendor/team-nifty-gmbh/tall-datatables/resources/js/tall-datatables.js',
                './vendor/team-nifty-gmbh/tall-calendar/resources/js/index.js',
                './vendor/wireui/wireui/dist/wireui.js'
            ],
            refresh: false
        })
    ],
    resolve: {
        preserveSymlinks: true
    }
});
