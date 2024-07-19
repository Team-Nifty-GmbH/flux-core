const defaultTheme = require('tailwindcss/defaultTheme')

module.exports = {
    presets: [
        require('./vendor/wireui/wireui/tailwind.config.js'),
        require('./vendor/team-nifty-gmbh/tall-calendar/tailwind.config.js'),
        require('./vendor/team-nifty-gmbh/tall-datatables/tailwind.config.js'),
    ],
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './app/Livewire/**/*.php',
        './app/Components/**/*.php',
        '.{{ relative_path }}/resources/**/*.blade.php',
        '.{{ relative_path }}/resources/**/*.js',
        '.{{ relative_path }}/resources/**/*.vue',
        '.{{ relative_path }}/src/Htmlables/**/*.php',
        '.{{ relative_path }}/src/Livewire/**/*.php',
        './vendor/wireui/wireui/resources/**/*.blade.php',
        './vendor/wireui/wireui/ts/**/*.ts',
        './vendor/wireui/wireui/src/View/**/*.php',
        './vendor/team-nifty-gmbh/tall-datatables/resources/views/**/*.blade.php',
        './vendor/team-nifty-gmbh/tall-datatables/resources/js/**/*.js',
        './vendor/team-nifty-gmbh/tall-calendar/resources/views/**/*.blade.php',
        './vendor/team-nifty-gmbh/tall-calendar/resources/js/**/*.js',
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
