import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/dashboard.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    // Separate Chart.js into its own chunk
                    'chartjs': ['chart.js'],

                    // Separate PDF-related libraries
                    'pdf-utils': ['jspdf', 'html2canvas'],

                    // Alpine.js in its own chunk
                    'alpine': ['alpinejs'],

                    // Other vendor libraries
                    'vendor': ['chart.js/auto']
                }
            }
        },
        // Increase chunk size warning limit to 1000kb to reduce noise
        chunkSizeWarningLimit: 1000
    }
});
