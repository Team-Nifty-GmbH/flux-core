<?php

test('$nuxbe is available as Alpine magic', function (): void {
    visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke()
        ->assertScript('typeof window.$nuxbe === "object"');
});

test('$nuxbe.format.money formats correctly', function (): void {
    $result = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke()
        ->script('() => window.$nuxbe.format.money(1234.56)');

    expect($result)->toContain('1')->toContain('234');
});

test('$nuxbe.format.money with colored option returns HTML', function (): void {
    $page = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();

    $positiveResult = $page->script('() => window.$nuxbe.format.money(100, {colored: true})');
    $negativeResult = $page->script('() => window.$nuxbe.format.money(-100, {colored: true})');

    expect($positiveResult)->toContain('emerald');
    expect($negativeResult)->toContain('red');
});

test('$nuxbe.format.percentage formats correctly', function (): void {
    $result = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke()
        ->script('() => window.$nuxbe.format.percentage(0.1567)');

    expect($result)->toContain('15');
});

test('$nuxbe.format.date formats ISO date', function (): void {
    $result = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke()
        ->script("() => window.\$nuxbe.format.date('2026-03-28')");

    expect($result)->toContain('2026');
});

test('$nuxbe.format.datetime formats ISO datetime', function (): void {
    $result = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke()
        ->script("() => window.\$nuxbe.format.datetime('2026-03-28T14:30:00')");

    expect($result)->toContain('2026')->toContain(':30');
});

test('$nuxbe.format.fileSize formats bytes', function (): void {
    $result = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke()
        ->script('() => window.$nuxbe.format.fileSize(1048576)');

    expect($result)->toBe('1MB');
});

test('$nuxbe.parseNumber parses numbers correctly', function (): void {
    $result = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke()
        ->script('() => window.$nuxbe.parseNumber(1234.5)');

    expect($result)->toBe('1234.50');
});

test('$nuxbe.format.badge returns HTML badge', function (): void {
    $result = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke()
        ->script("() => window.\$nuxbe.format.badge('Active', 'green')");

    expect($result)->toContain('Active')->toContain('green');
});

test('$nuxbe.format.relativeTime returns human readable time', function (): void {
    $result = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke()
        ->script('() => window.$nuxbe.format.relativeTime(Date.now() - 60000)');

    expect($result)->not->toBeEmpty();
});
