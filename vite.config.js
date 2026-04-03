import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    base: '/flux/',
    build: {
        outDir: 'dist',
        emptyOutDir: true,
    },
    plugins: [
        tailwindcss(),
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/sw.js',
                'resources/js/web-push.js',
            ],
            refresh: false,
        }),
    ],
});
