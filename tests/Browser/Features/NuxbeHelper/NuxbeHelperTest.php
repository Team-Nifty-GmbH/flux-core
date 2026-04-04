<?php

test('$nuxbe is available as Alpine magic', function (): void {
    $page = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();

    $nuxbeExists = $page->script(<<<'JS'
        () => typeof window.$nuxbe === 'object'
    JS);

    expect($nuxbeExists)->toBeTrue();
    $page->assertNoJavascriptErrors();
});

test('$nuxbe.format.money formats correctly', function (): void {
    $page = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();

    $result = $page->script(<<<'JS'
        () => window.$nuxbe.format.money(1234.56)
    JS);

    expect($result)->toContain('1');
    expect($result)->toContain('234');
    $page->assertNoJavascriptErrors();
});

test('$nuxbe.format.money with colored option returns HTML', function (): void {
    $page = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();

    $positiveResult = $page->script(<<<'JS'
        () => window.$nuxbe.format.money(100, {colored: true})
    JS);

    $negativeResult = $page->script(<<<'JS'
        () => window.$nuxbe.format.money(-100, {colored: true})
    JS);

    expect($positiveResult)->toContain('emerald');
    expect($negativeResult)->toContain('red');
    $page->assertNoJavascriptErrors();
});

test('$nuxbe.format.percentage formats correctly', function (): void {
    $page = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();

    $result = $page->script(<<<'JS'
        () => window.$nuxbe.format.percentage(0.1567)
    JS);

    expect($result)->toContain('15');
    $page->assertNoJavascriptErrors();
});

test('$nuxbe.format.date formats ISO date', function (): void {
    $page = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();

    $result = $page->script(<<<'JS'
        () => window.$nuxbe.format.date('2026-03-28')
    JS);

    expect($result)->toContain('2026');
    $page->assertNoJavascriptErrors();
});

test('$nuxbe.format.datetime formats ISO datetime', function (): void {
    $page = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();

    $result = $page->script(<<<'JS'
        () => window.$nuxbe.format.datetime('2026-03-28T14:30:00')
    JS);

    expect($result)->toContain('2026');
    expect($result)->toContain(':30');
    $page->assertNoJavascriptErrors();
});

test('$nuxbe.format.fileSize formats bytes', function (): void {
    $page = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();

    $result = $page->script(<<<'JS'
        () => window.$nuxbe.format.fileSize(1048576)
    JS);

    expect($result)->toBe('1MB');
    $page->assertNoJavascriptErrors();
});

test('$nuxbe.parseNumber parses numbers correctly', function (): void {
    $page = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();

    $result = $page->script(<<<'JS'
        () => window.$nuxbe.parseNumber(1234.5)
    JS);

    expect($result)->toBe('1234.50');
    $page->assertNoJavascriptErrors();
});

test('$nuxbe.format.badge returns HTML badge', function (): void {
    $page = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();

    $result = $page->script(<<<'JS'
        () => window.$nuxbe.format.badge('Active', 'green')
    JS);

    expect($result)->toContain('Active');
    expect($result)->toContain('green');
    $page->assertNoJavascriptErrors();
});

test('$nuxbe.format.relativeTime returns human readable time', function (): void {
    $page = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();

    $result = $page->script(<<<'JS'
        () => window.$nuxbe.format.relativeTime(Date.now() - 60000)
    JS);

    // Should contain "1" and some time unit
    expect($result)->not->toBeEmpty();
    $page->assertNoJavascriptErrors();
});
