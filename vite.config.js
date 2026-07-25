import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';
import { VitePWA } from 'vite-plugin-pwa';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.tsx',
            refresh: true,
        }),
        react(),
        VitePWA({
            registerType: 'autoUpdate',
            includeAssets: [],
            manifest: {
                name: 'ARKA MineOps',
                short_name: 'MineOps',
                description: 'Integrated Mining Operations Dashboard — PT. Arkananta',
                theme_color: '#1677ff',
                background_color: '#ffffff',
                display: 'standalone',
                start_url: '/dashboard',
                lang: 'id',
                icons: [
                    {
                        src: '/favicon.ico',
                        sizes: '64x64',
                        type: 'image/x-icon',
                    },
                ],
            },
            workbox: {
                globPatterns: ['**/*.{js,css,html,ico,png,svg,woff2}'],
                navigateFallback: '/dashboard',
                runtimeCaching: [
                    {
                        urlPattern: /^https:\/\/fonts\.bunny\.net\/.*/i,
                        handler: 'CacheFirst',
                        options: {
                            cacheName: 'bunny-fonts',
                            expiration: { maxEntries: 10, maxAgeSeconds: 60 * 60 * 24 * 365 },
                        },
                    },
                ],
            },
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
        },
        dedupe: ['dayjs', 'react', 'react-dom'],
    },
});
