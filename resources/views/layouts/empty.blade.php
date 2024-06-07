<!DOCTYPE html>
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        ...
        @livewireStyles
    </head>
    <body>
    @vite('packages/flux-core/resources/js/alpine.js')
    @livewireScripts
    {{ $slot}}
    </body>
    </html>
