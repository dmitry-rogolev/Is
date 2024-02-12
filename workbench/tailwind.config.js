import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './skeleton/vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './skeleton/vendor/laravel/jetstream/**/*.blade.php',
        './skeleton/storage/framework/views/*.php',
        './skeleton/resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms, typography],
};
