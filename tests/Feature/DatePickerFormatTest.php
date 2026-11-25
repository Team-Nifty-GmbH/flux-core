<?php

use Illuminate\Support\Facades\Blade;

test('renders the date picker in the format of the locale', function (): void {
    app()->setLocale('de');

    $html = Blade::render('<x-date />');

    expect($html)->toContain('DD.MM.YYYY');
    expect($html)->not->toContain('YYYY-MM-DD');
});

test('renders the date picker in the format of the english locale', function (): void {
    app()->setLocale('en');

    $html = Blade::render('<x-date />');

    expect($html)->toContain('MM\/DD\/YYYY');
});

test('keeps a date format that was passed explicitly', function (): void {
    app()->setLocale('de');

    $html = Blade::render('<x-date format="MMMM YYYY" />');

    expect($html)->toContain('MMMM YYYY');
    expect($html)->not->toContain('DD.MM.YYYY');
});

test('renders the time picker on a twenty four hour clock in every locale', function (string $locale): void {
    app()->setLocale($locale);

    $html = Blade::render('<x-time />');

    expect($html)->not->toContain('AM');
})->with(['de', 'en', 'si', 'lb']);

test('renders a stored time in a locale that writes twelve hours', function (): void {
    app()->setLocale('en');

    $html = Blade::render('<x-time value="14:30" />');

    expect($html)->toContain('14:30');
});
