import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindNested from 'tailwindcss/nesting';
import tailwind from 'tailwindcss';
import autoprefixer from 'autoprefixer';

export default defineConfig({
    build: {
        target: 'esnext'
    },

    server: {
        host: true, // need to listen on all interfaces instead 127.0.0.1
        port: 3000,
        strictPort: true,

        hmr: {
            host: 'localhost' // needs to resolve correct address instead ipv6 (development only)
        },

        watch: {
            ignored: [
                '**/.idea/**',
                '**/logs/**',
                '**/storage/**',
                '**/tests/**',
                '**/vendor/**',
                '**/**.php',
                '**/**.min.js'
            ]
        }
    },

    resolve: {
        extensions: ['.ts', '.js', '.vue', 'index.js', 'index.ts']
    },

    css: {
        postcss: {
            plugins: [
               tailwindNested,
               tailwind(),
               autoprefixer
            ]
        }
    },

    plugins: [
        vue(),
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.ts',

                'resources/css/auth.css',
                'resources/js/auth.ts',

                'resources/css/admin.css'
            ],
            refresh: true
        })
    ]
});
