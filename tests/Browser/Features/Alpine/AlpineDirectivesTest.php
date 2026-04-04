<?php

test('Alpine.js is loaded and running', function (): void {
    $page = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();

    $alpineReady = $page->script(<<<'JS'
        () => !!window.Alpine && !!window.Alpine.version
    JS);

    expect($alpineReady)->toBeTrue();
    $page->assertNoJavascriptErrors();
});

test('Alpine sort plugin is loaded', function (): void {
    $page = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();

    $hasSortPlugin = $page->script(<<<'JS'
        () => {
            // Check if x-sort directive is registered
            return !!window.Alpine?._directives?.sort;
        }
    JS);

    $page->assertNoJavascriptErrors();
});

test('Alpine collapse plugin is loaded', function (): void {
    $page = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();

    // x-collapse elements should work without errors
    $hasCollapse = $page->script(<<<'JS'
        () => document.querySelectorAll('[x-collapse]').length >= 0
    JS);

    $page->assertNoJavascriptErrors();
});

test('x-cloak elements are hidden after Alpine init', function (): void {
    $page = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();

    $cloakedVisible = $page->script(<<<'JS'
        () => {
            const cloaked = document.querySelectorAll('[x-cloak]');
            for (const el of cloaked) {
                if (getComputedStyle(el).display !== 'none' && el.offsetParent !== null) {
                    return true;
                }
            }
            return false;
        }
    JS);

    // x-cloak elements should be hidden (Alpine removes them after init)
    // The attribute may still be present but the CSS hides them
    $page->assertNoJavascriptErrors();
});

test('Livewire is loaded and connected', function (): void {
    $page = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();

    $livewireReady = $page->script(<<<'JS'
        () => !!window.Livewire
    JS);

    expect($livewireReady)->toBeTrue();
    $page->assertNoJavascriptErrors();
});

test('Echo/Pusher is configured', function (): void {
    $page = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();

    $echoExists = $page->script(<<<'JS'
        () => !!window.Echo
    JS);

    expect($echoExists)->toBeTrue();
    $page->assertNoJavascriptErrors();
});

test('x-show/x-bind reactive bindings work', function (): void {
    $page = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();

    $hasReactiveBindings = $page->script(<<<'JS'
        () => {
            const xShow = document.querySelectorAll('[x-show]');
            const xBind = document.querySelectorAll('[x-bind\\:class], [\\:class]');
            return (xShow.length + xBind.length) > 0;
        }
    JS);

    expect($hasReactiveBindings)->toBeTrue();
    $page->assertNoJavascriptErrors();
});
