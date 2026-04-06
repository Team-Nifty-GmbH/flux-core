<?php

use FluxErp\Helpers\Helper;

test('getHtmlInputFieldTypes returns expected types', function (): void {
    $types = Helper::getHtmlInputFieldTypes();

    expect($types)->toBeArray()
        ->toContain('text', 'number', 'email', 'date', 'checkbox', 'select');
});

test('buildRepeatStringFromArray for days', function (): void {
    $result = Helper::buildRepeatStringFromArray([
        'unit' => 'days',
        'interval' => 3,
        'start' => '2026-01-01',
    ]);

    expect($result)->toBe('+3 days');
});

test('buildRepeatStringFromArray for years', function (): void {
    $result = Helper::buildRepeatStringFromArray([
        'unit' => 'years',
        'interval' => 1,
        'start' => '2026-01-01',
    ]);

    expect($result)->toBe('+1 years');
});

test('buildRepeatStringFromArray for months with day', function (): void {
    $result = Helper::buildRepeatStringFromArray([
        'unit' => 'months',
        'interval' => 2,
        'monthly' => 'day',
        'start' => '2026-01-15',
    ]);

    expect($result)->toBe('+2 months');
});

test('buildRepeatStringFromArray for months with first', function (): void {
    $result = Helper::buildRepeatStringFromArray([
        'unit' => 'months',
        'interval' => 1,
        'monthly' => 'first',
        'start' => '2026-01-06', // a Monday
    ]);

    expect($result)->toContain('first');
    expect($result)->toContain('1 months');
});

test('parseRepeatStringToArray for days', function (): void {
    $result = Helper::parseRepeatStringToArray('+3 days');

    expect($result['unit'])->toBe('days');
    expect($result['interval'])->toBe('3');
});

test('parseRepeatStringToArray for years', function (): void {
    $result = Helper::parseRepeatStringToArray('+1 years');

    expect($result['unit'])->toBe('years');
    expect($result['interval'])->toBe('1');
});

test('parseRepeatStringToArray for months with day', function (): void {
    $result = Helper::parseRepeatStringToArray('+2 months');

    expect($result['unit'])->toBe('months');
    expect($result['monthly'])->toBe('day');
});

test('classExists returns class for valid model', function (): void {
    $result = Helper::classExists('Order', isModel: true);

    expect($result)->toBe(FluxErp\Models\Order::class);
});

test('classExists returns false for invalid model', function (): void {
    $result = Helper::classExists('NonExistentModel', isModel: true);

    expect($result)->toBeFalse();
});

test('classExists returns full class when already FQCN', function (): void {
    $result = Helper::classExists(FluxErp\Models\Order::class, isModel: true);

    expect($result)->toBe(FluxErp\Models\Order::class);
});
