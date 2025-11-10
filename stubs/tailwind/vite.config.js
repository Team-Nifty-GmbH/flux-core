import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                '.{{ relative_path }}/resources/css/app.css',
                '.{{ relative_path }}/resources/js/app.js',
                '.{{ relative_path }}/resources/js/apex-charts.js',
                '.{{ relative_path }}/resources/js/alpine.js',
                '.{{ relative_path }}/resources/js/sw.js',
                '.{{ relative_path }}/resources/js/web-push.js',
                '.{{ relative_path }}/resources/js/print-editor.js',
                './vendor/team-nifty-gmbh/tall-datatables/resources/js/tall-datatables.js',
            ],
            refresh: false,
        }),
    ],
});
