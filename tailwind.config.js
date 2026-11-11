import defaultTheme from 'tailwindcss/defaultTheme';
import colors from 'tailwindcss/colors';
import forms from '@tailwindcss/forms';

/**
 * CPSU RAS design tokens
 * - primary: forest green pulled from the university seal, desaturated for UI use
 * - gold:    ochre accent (the seal's yellow, toned down so it reads on white)
 * - gray:    warm stone neutrals so the "paper" surfaces don't look clinical
 */
/** @type {import('tailwindcss').Config} */
export default {
    // The app has no dark theme; stray `dark:` classes must not trigger from OS settings.
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"IBM Plex Sans"', ...defaultTheme.fontFamily.sans],
                serif: ['"Source Serif 4"', 'Georgia', ...defaultTheme.fontFamily.serif],
                mono: ['"IBM Plex Mono"', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                gray: colors.stone,
                paper: '#F6F5F1',
                primary: {
                    50: '#F2F6F3',
                    100: '#E3ECE6',
                    200: '#C6D8CC',
                    300: '#9DBBA7',
                    400: '#6E977C',
                    500: '#46735A',
                    600: '#2F5C42',
                    700: '#234A34',
                    800: '#1A3A28',
                    900: '#13291D',
                    950: '#0B1A12',
                },
                gold: {
                    50: '#FBF8EC',
                    100: '#F6EDCB',
                    200: '#EDDB98',
                    300: '#E2C665',
                    400: '#D7B13E',
                    500: '#C69B22',
                    600: '#A6801A',
                    700: '#836415',
                    800: '#634C12',
                    900: '#4A390E',
                    950: '#2E2307',
                },
            },
            boxShadow: {
                card: '0 1px 0 rgba(19, 41, 29, 0.04), 0 1px 2px rgba(19, 41, 29, 0.06)',
            },
            borderRadius: {
                // Pull the oversized "pill everything" radii back to something calmer.
                xl: '0.625rem',
                '2xl': '0.75rem',
                '3xl': '1rem',
            },
        },
    },

    plugins: [forms],
};
