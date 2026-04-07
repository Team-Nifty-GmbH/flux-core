<?php

use FluxErp\Support\Calculation\Rounding;

test('round to 2 decimals', function (): void {
    expect(Rounding::round(10.456, 2))->toBe('10.46');
    expect(Rounding::round(10.454, 2))->toBe('10.45');
    expect(Rounding::round(10.455, 2))->toBe('10.46');
});

test('round null returns 0', function (): void {
    expect(Rounding::round(null))->toEqual(0);
});

test('ceil rounds up', function (): void {
    expect(Rounding::ceil(10.451, 2))->toBe('10.46');
    expect(Rounding::ceil(10.001, 2))->toBe('10.01');
});

test('ceil null returns 0', function (): void {
    expect(Rounding::ceil(null))->toEqual(0);
});

test('floor rounds down', function (): void {
    expect(Rounding::floor(10.459, 2))->toBe('10.45');
    expect(Rounding::floor(10.999, 2))->toBe('10.99');
});

test('floor null returns 0', function (): void {
    expect(Rounding::floor(null))->toEqual(0);
});

test('nearest rounds to nearest multiple', function (): void {
    expect(Rounding::nearest(5, 10.47, 2))->toBe('10.45');
    expect(Rounding::nearest(5, 10.48, 2))->toBe('10.50');
});

test('nearest with ceil mode', function (): void {
    expect(Rounding::nearest(5, 10.41, 2, 'ceil'))->toBe('10.45');
});

test('nearest with floor mode', function (): void {
    expect(Rounding::nearest(5, 10.49, 2, 'floor'))->toBe('10.45');
});

test('end rounds to end with number', function (): void {
    expect(Rounding::end(9, 10.43, 2))->toBe('10.39');
    expect(Rounding::end(9, 10.95, 2))->toBe('10.99');
});

test('end with matching value returns unchanged', function (): void {
    expect(Rounding::end(9, 10.49, 2))->toBe('10.49');
});

test('round with 0 precision', function (): void {
    expect(Rounding::round(10.6, 0))->toBe('11');
    expect(Rounding::round(10.4, 0))->toBe('10');
});
