import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
<<<<<<< HEAD
import tailwindcss from "@tailwindcss/vite";
=======
import tailwindcss from '@tailwindcss/vite';
>>>>>>> feature/auto-inject-frontend-assets

export default defineConfig({
    base: '/flux/',
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
