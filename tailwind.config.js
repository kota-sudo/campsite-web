import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                forest: {
                    50:  '#f0f7eb',
                    100: '#d5ecc4',
                    200: '#b0d896',
                    300: '#a8d878',
                    400: '#7abe50',
                    500: '#5a9e3a',
                    600: '#2d5a1b',
                    700: '#1c3a0e',
                    800: '#142e09',
                    900: '#0d2208',
                },
                campfire: {
                    100: '#fde8d3',
                    300: '#f5c49a',
                    400: '#f5a623',
                    500: '#e07b39',
                    600: '#c4621a',
                    700: '#a34f14',
                },
                parchment: {
                    50:  '#faf7f2',
                    100: '#f5f0e8',
                    200: '#f0ece3',
                    300: '#e0d8cc',
                    400: '#c8bfb0',
                },
            },
        },
    },

    plugins: [forms],
};
