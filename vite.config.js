import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/header.css',
                'resources/js/main.ts',
                'resources/js/lightgallery.ts'
            ],
            refresh: true,
        }),
    ],
});
