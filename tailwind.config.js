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
            colors: {
                itsc: {
                    red: {
                        50: '#FDECEF',
                        100: '#FAD1D8',
                        500: '#D81E3C',
                        600: '#C8102E',
                        700: '#A50D26',
                    },
                    slate: {
                        50: '#F4F6F8',
                        100: '#E3E8ED',
                        300: '#B9C2CB',
                        500: '#4A5968',
                        700: '#2E3B47',
                        900: '#1B242C',
                    },
                },
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
