import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.tsx',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    navy: '#1a3a6a',
                    'navy-dark': '#142c52',
                    'navy-light': '#e8edf5',
                    gold: '#b8860b',
                    'gold-dark': '#96690a',
                    'gold-light': '#e6b422',
                },
            },
        },
    },

    plugins: [forms],
};
