const defaultTheme = require('tailwindcss/defaultTheme')

module.exports = {
    presets: [require('./vendor/wireui/wireui/tailwind.config.js')],
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './vendor/wireui/wireui/resources/**/*.blade.php',
        './vendor/wireui/wireui/ts/**/*.ts',
        './vendor/wireui/wireui/src/View/**/*.php',
        './vendor/team-nifty-gmbh/tall-datatables/resources/views/**/*.blade.php',
        './vendor/team-nifty-gmbh/tall-datatables/resources/js/**/*.js',
    ],
    safelist: [
        'bg-slate-600',
        'bg-gray-600',
        'bg-zinc-600',
        'bg-neutral-600',
        'bg-stone-600',
        'bg-red-600',
        'bg-orange-600',
        'bg-amber-600',
        'bg-yellow-600',
        'bg-lime-600',
        'bg-green-600',
        'bg-emerald-600',
        'bg-teal-600',
        'bg-cyan-600',
        'bg-sky-600',
        'bg-blue-600',
        'bg-indigo-600',
        'bg-violet-600',
        'bg-purple-600',
        'bg-fuchsia-600',
        'bg-pink-600',
        'bg-rose-600',
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
}
