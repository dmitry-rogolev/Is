import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'skeleton/resources/css/app.css',
                'skeleton/resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    root: 'skeleton', 
});
