import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            refresh: true,
            buildDirectory: 'build',
        }),
        react(),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
        },
    },
    server: {
        hmr: {
            host: 'localhost',
        },
    },
    build: {
        manifest: true,
        rollupOptions: {
            input: {
                app: 'resources/js/app.tsx',
                styles: 'resources/css/app.css',
            },
        },
    },
});
