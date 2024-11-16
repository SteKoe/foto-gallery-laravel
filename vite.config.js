import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/admin.css',
                'resources/views/components/alert/alert.css',
                'resources/views/components/form/checkbox.css',
                'resources/css/header.css',
                'resources/js/main.ts',
                'resources/js/lightgallery.ts'
            ],
            refresh: true,
        }),
    ],
});
