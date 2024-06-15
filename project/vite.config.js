import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        vue(),
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            watch: {
                usePolling: true
            },
        })
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        hmr: {
            port: 5173,
            clientPort: 5173,
            host: 'localhost'
        }
    },
    resolve: {
        alias: {
            '@': '/resources/js',
        }
    }
});
