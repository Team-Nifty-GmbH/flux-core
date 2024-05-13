import {defineConfig} from 'vite';
import laravel from 'laravel-vite-plugin';
import livewire from '@defstudio/vite-livewire-plugin';

export default defineConfig({
    base: "/flux-assets/",
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/apex-charts.js',
                'resources/js/alpine.js',
                'resources/js/sw.js',
                'resources/js/web-push.js',
            ],
            refresh: false
        }),
        livewire({
            refresh: ['resources/css/app.css'],
        }),
    ],
});
