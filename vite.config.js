import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { resolve } from 'path';
import { defineConfig, splitVendorChunkPlugin } from 'vite';

export default defineConfig({
    resolve: {
        alias: {
            '@': resolve('resources'),
            'tailwind.config': resolve('tailwind.config.js'),
        },
    },
    plugins: [
        laravel({
            input: 'resources/ts/app.ts',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        splitVendorChunkPlugin(),
    ],
});
