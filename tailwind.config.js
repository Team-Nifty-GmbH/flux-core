const defaultTheme = require('tailwindcss/defaultTheme')

module.exports = {
    darkMode: 'selector',
    content: [
        __dirname + '/resources/**/*.blade.php',
        __dirname + '/resources/**/*.js',
        __dirname + '/resources/**/*.vue',
        __dirname + '/src/Htmlables/**/*.php',
        __dirname + '/src/Livewire/**/*.php',
    ],
    theme: {
        extend: {
            transitionProperty: {
                width: 'width',
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
        },
    },
    plugins: [require('@tailwindcss/typography'), require('@tailwindcss/forms')],
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
