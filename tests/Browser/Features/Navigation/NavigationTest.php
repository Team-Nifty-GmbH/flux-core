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

test('loading overlay exists in DOM', function (): void {
    visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke()
        ->assertScript("!!document.getElementById('loading-overlay-spinner')");
});
