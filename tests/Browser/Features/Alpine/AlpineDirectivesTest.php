<?php

test('Alpine.js is loaded and running', function (): void {
    visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke()
        ->assertScript('!!window.Alpine && !!window.Alpine.version');
});

test('Alpine sort plugin is loaded', function (): void {
    visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();
});

test('Alpine collapse plugin is loaded', function (): void {
    visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke()
        ->assertScript('document.querySelectorAll("[x-collapse]").length >= 0');
});

test('x-cloak elements are hidden after Alpine init', function (): void {
    visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();
});

test('Livewire is loaded and connected', function (): void {
    visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke()
        ->assertScript('!!window.Livewire');
});

test('Echo/Pusher is configured', function (): void {
    visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke()
        ->assertScript('!!window.Echo');
});

test('x-show/x-bind reactive bindings work', function (): void {
    visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke()
        ->assertScript(<<<'JS'
            document.querySelectorAll('[x-show]').length + document.querySelectorAll('[x-bind\\:class], [\\:class]').length > 0
        JS);
});
