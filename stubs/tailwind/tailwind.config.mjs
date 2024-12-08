/** @type {import('tailwindcss').Config} */
import fluxConfig from '.{{ relative_path }}/tailwind.config.mjs';
import wireuiConfig from './vendor/wireui/wireui/tailwind.config.js';
import tallCalendar from './vendor/team-nifty-gmbh/tall-calendar/tailwind.config.mjs';
import dataTablesConfig from './vendor/team-nifty-gmbh/tall-datatables/tailwind.config.mjs';

export default {
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
        './vendor/wireui/wireui/src/*.php',
        './vendor/wireui/wireui/ts/**/*.ts',
        './vendor/wireui/wireui/src/WireUi/**/*.php',
        './vendor/wireui/wireui/src/Components/**/*.php',
    ].concat(
        tallCalendar.content,
        dataTablesConfig.content,
        fluxConfig.content
    ),
}
