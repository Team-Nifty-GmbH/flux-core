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

test('renders the time picker without meridiem in german', function (): void {
    app()->setLocale('de');

    $html = Blade::render('<x-time />');

    expect($html)->not->toContain('AM');
});

test('renders the time picker with meridiem in english', function (): void {
    app()->setLocale('en');

    $html = Blade::render('<x-time />');

    expect($html)->toContain('AM');
});

test('keeps a time format that was passed explicitly', function (): void {
    app()->setLocale('en');

    $html = Blade::render('<x-time format="24" />');

    expect($html)->not->toContain('AM');
});
