const defaultTheme = require('tailwindcss/defaultTheme')
const fluxConfig = require('./tailwind.config.js')
const wireuiConfig = require('./vendor/wireui/wireui/tailwind.config.js')
const tallCalendar = require('./vendor/team-nifty-gmbh/tall-calendar/tailwind.config.js')
const dataTablesConfig = require('./vendor/team-nifty-gmbh/tall-datatables/tailwind.config.js')

module.exports = {
    presets: [
        wireuiConfig,
        dataTablesConfig,
        tallCalendar,
        fluxConfig,
    ],
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './app/Livewire/**/*.php',
        './app/Components/**/*.php',
        './vendor/wireui/wireui/resources/**/*.blade.php',
        './vendor/wireui/wireui/ts/**/*.ts',
        './vendor/wireui/wireui/src/View/**/*.php',
    ].concat(
        tallCalendar.content,
        dataTablesConfig.content,
        fluxConfig.content
    ),
}
