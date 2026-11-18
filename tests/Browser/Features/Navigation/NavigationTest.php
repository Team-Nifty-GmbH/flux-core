<?php

test('sidebar navigation renders without js errors', function (): void {
    visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();
});

test('sidebar has navigation links', function (): void {
    visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke()
        ->assertScript("document.querySelectorAll('aside a[href], nav a[wire\\\\:navigate]').length > 0");
});

test('wire:navigate preserves layout on page change', function (): void {
    $page = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();

    $page->script(<<<'JS'
        () => {
            const link = document.querySelector('a[href*="contacts"], a[href*="orders"]');
            if (link) link.click();
        }
    JS);

    $page->wait(3)
        ->assertScript("!!document.querySelector('aside, nav')");
});

test('sidebar stays collapsed while the pointer only passes over it', function (): void {
    $menuOpen = "Alpine.\$data(document.getElementById('main-navigation')).menuOpen";

    visit(route('dashboard'))
        ->assertNoSmoke()
        ->hover('main >> nth=0')
        ->hover('#main-navigation nav a >> nth=0')
        ->assertScript("{$menuOpen} === false")
        ->hover('main >> nth=0')
        ->wait(0.6)
        ->assertScript("{$menuOpen} === false");
});

test('sidebar stays collapsed when the pointer leaves the window over it', function (): void {
    $menuOpen = "Alpine.\$data(document.getElementById('main-navigation')).menuOpen";

    $page = visit(route('dashboard'))
        ->assertNoSmoke()
        ->hover('main >> nth=0')
        ->hover('#main-navigation nav a >> nth=0');

    $page->script(<<<'JS'
        () => document
            .querySelector('#main-navigation > div')
            .dispatchEvent(new MouseEvent('mouseleave'))
    JS);

    $page->wait(0.6)
        ->assertScript("{$menuOpen} === false");
});

test('sidebar expands once the pointer rests on it', function (): void {
    visit(route('dashboard'))
        ->assertNoSmoke()
        ->hover('main >> nth=0')
        ->hover('#main-navigation nav a >> nth=0')
        ->wait(0.6)
        ->assertScript("Alpine.\$data(document.getElementById('main-navigation')).menuOpen === true");
});

test('loading overlay exists in DOM', function (): void {
    visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke()
        ->assertScript("!!document.getElementById('loading-overlay-spinner')");
});
