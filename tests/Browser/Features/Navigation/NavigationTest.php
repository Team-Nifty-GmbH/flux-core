<?php

test('sidebar navigation renders without js errors', function (): void {
    visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke()
        ->assertNoJavascriptErrors();
});

test('sidebar has navigation links', function (): void {
    $page = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();

    $linkCount = $page->script(<<<'JS'
        () => document.querySelectorAll('aside a[href], nav a[wire\\:navigate]').length
    JS);

    expect($linkCount)->toBeGreaterThan(0);
    $page->assertNoJavascriptErrors();
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

    $page->script('() => new Promise(r => setTimeout(r, 3000))');

    $hasSidebar = $page->script(<<<'JS'
        () => !!document.querySelector('aside, nav')
    JS);

    expect($hasSidebar)->toBeTrue();
    $page->assertNoJavascriptErrors();
});

test('loading overlay exists in DOM', function (): void {
    $page = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();

    $hasOverlay = $page->script(<<<'JS'
        () => !!document.getElementById('loading-overlay-spinner')
    JS);

    expect($hasOverlay)->toBeTrue();
    $page->assertNoJavascriptErrors();
});
