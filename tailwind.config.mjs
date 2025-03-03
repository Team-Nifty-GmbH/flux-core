import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

export default {
    darkMode: 'selector',
    content: [
        __dirname + '/resources/**/*.blade.php',
        __dirname + '/resources/**/*.js',
        __dirname + '/resources/**/*.vue',
        __dirname + '/src/Htmlables/**/*.php',
        __dirname + '/src/Providers/*.php',
        __dirname + '/src/View/Components/**/*.php',
        __dirname + '/src/Livewire/**/*.php',
    ],
    theme: {
        extend: {
            colors: {
                'flux-primary': {
                    '800': '#0690FA',
                    '700': '#2FA1F7',
                    '600': '#5EB3F4',
                    '500': '#74BBF3',
                    '400': '#8AC4F1',
                    '300': '#9ECDF0',
                    '200': '#B6D6ED',
                    '100': '#CCDEEB',
                    '50': '#D7E3EC',
                },
                'flux-secondary': {
                    '50': '#D7DBE2',
                    '100': '#B5B9C5',
                    '200': '#9EA2B4',
                    '300': '#5B5D7A',
                    '400': '#444667',
                    '500': '#2D2F55',
                    '600': '#171842',
                    '700': '#00012F',
                    '800': '#00011B',
                },
            },
            transitionProperty: {
                width: 'width',
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
        },
    },
    plugins: [typography, forms],
    safelist: [
        'mention',
        'tippy-box',
        'ring-offset-2',
        'indent-icon',
        'md:col-span-1',
        'md:col-span-2',
        'md:col-span-3',
        'md:col-span-4',
        'md:col-span-5',
        'md:col-span-6',
        'md:col-span-7',
        'md:col-span-8',
        'md:col-span-9',
        'md:col-span-10',
        'md:col-span-11',
        'md:col-span-12',
        {
            pattern: /row-span-\d+/
        },
        {
            pattern: /grid-cols-\d+/
        }
    ]
}
