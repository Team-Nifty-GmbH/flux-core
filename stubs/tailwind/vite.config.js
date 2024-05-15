import {defineConfig} from 'vite';
import laravel from 'laravel-vite-plugin';

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
                './vendor/wireui/wireui/dist/wireui.js'
            ],
            refresh: false
        })
    ],
});
