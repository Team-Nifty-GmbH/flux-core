<?php

use FluxErp\Rules\Numeric;
use Illuminate\Support\Facades\Validator;

function validateNumeric(mixed $value, ?float $min = null, ?float $max = null): bool
{
    return Validator::make(['val' => $value], ['val' => app(Numeric::class, compact('min', 'max'))])->passes();
}

test('valid number passes', function (): void {
    expect(validateNumeric(42))->toBeTrue();
    expect(validateNumeric('3.14'))->toBeTrue();
    expect(validateNumeric(0))->toBeTrue();
});

test('non-numeric fails', function (): void {
    expect(validateNumeric('abc'))->toBeFalse();
});

test('min constraint works', function (): void {
    expect(validateNumeric(5, min: 0))->toBeTrue();
    expect(validateNumeric(-1, min: 0))->toBeFalse();
    expect(validateNumeric(0, min: 0))->toBeTrue();
});

test('max constraint works', function (): void {
    expect(validateNumeric(0.5, max: 1))->toBeTrue();
    expect(validateNumeric(1.5, max: 1))->toBeFalse();
    expect(validateNumeric(1, max: 1))->toBeTrue();
});

test('min and max together', function (): void {
    expect(validateNumeric(0.5, min: 0, max: 1))->toBeTrue();
    expect(validateNumeric(-0.1, min: 0, max: 1))->toBeFalse();
    expect(validateNumeric(1.1, min: 0, max: 1))->toBeFalse();
});

test('high precision values work', function (): void {
    expect(validateNumeric('0.000000001', min: 0))->toBeTrue();
    expect(validateNumeric('999999999.999999999', max: 1000000000))->toBeTrue();
});
