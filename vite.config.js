// import { defineConfig } from 'vite';
// import laravel from 'laravel-vite-plugin';
// import tailwindcss from '@tailwindcss/vite';

// export default defineConfig({
//     base: '/future-new/public/',
//     plugins: [
//         laravel({
//             input: ['resources/css/app.css', 'resources/js/app.js'],
//             refresh: true,
//         }),
//         tailwindcss(),
//     ],
//     define: {
//         global: 'globalThis',
//     },
//     resolve: {
//         alias: {
//             '$': 'jquery',
//             'jQuery': 'jquery',
//         }
//     },
//     optimizeDeps: {
//         include: ['jquery', 'bootstrap', 'lodash']
//     }
// });


import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    define: {
        global: 'globalThis',
    },
    resolve: {
        alias: {
            '$': 'jquery',
            'jQuery': 'jquery',
        }
    },
    optimizeDeps: {
        include: ['jquery', 'bootstrap', 'lodash']
    },
    server: {
        hmr: {
            host: 'localhost',
        },
        // Proxy all non-Vite assets to your Laravel application
        proxy: {
            // Proxy Backpack assets
            '/graindashboard': {
                target: 'http://localhost/future-new/public',
                changeOrigin: true,
                secure: false
            },
            // Proxy other vendor assets
            '/vendor': {
                target: 'http://localhost/future-new/public',
                changeOrigin: true,
                secure: false
            },
            // Proxy storage assets
            '/storage': {
                target: 'http://localhost/future-new/public',
                changeOrigin: true,
                secure: false
            },
            // Proxy any other static assets that might be needed
            '^(?!/build).*\\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|eot)': {
                target: 'http://localhost/future-new/public',
                changeOrigin: true,
                secure: false
            }
        }
    }
});

// import { defineConfig } from 'vite';
// import laravel from 'laravel-vite-plugin';
// import tailwindcss from '@tailwindcss/vite';

// export default defineConfig({
//     plugins: [
//         laravel({
//             input: ['resources/css/app.css', 'resources/js/app.js'],
//             refresh: true,
//         }),
//         tailwindcss(),
//     ],
// });