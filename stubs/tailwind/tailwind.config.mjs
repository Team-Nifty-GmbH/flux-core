/** @type {import('tailwindcss').Config} */
import fluxConfig from '.{{ relative_path }}/tailwind.config.mjs';
import tallstackuiConfig from './vendor/tallstackui/tallstackui/tailwind.config.js';
import tallCalendar from './vendor/team-nifty-gmbh/tall-calendar/tailwind.config.mjs';
import dataTablesConfig from './vendor/team-nifty-gmbh/tall-datatables/tailwind.config.mjs';
import forms from '@tailwindcss/forms';

export default {
    presets: [
        tallstackuiConfig,
        dataTablesConfig,
        tallCalendar,
        fluxConfig,
    ],
    plugins: [
        forms,
    ],
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './app/Livewire/**/*.php',
        './app/Components/**/*.php',
        './vendor/tallstackui/tallstackui/src/**/*.php',
    ].concat(
        tallCalendar.content,
        dataTablesConfig.content,
        fluxConfig.content
    ),
}
