<?php

use FluxErp\Rules\Numeric;
use Illuminate\Support\Facades\Validator;

beforeEach(function (): void {
    $this->validate = fn (mixed $value, ?float $min = null, ?float $max = null) => Validator::make(
        ['val' => $value],
        ['val' => app(Numeric::class, compact('min', 'max'))]
    )->passes();
});

test('valid number passes', function (): void {
    expect(($this->validate)(42))->toBeTrue();
    expect(($this->validate)('3.14'))->toBeTrue();
    expect(($this->validate)(0))->toBeTrue();
});

test('non-numeric fails', function (): void {
    expect(($this->validate)('abc'))->toBeFalse();
});

test('min constraint works', function (): void {
    expect(($this->validate)(5, min: 0))->toBeTrue();
    expect(($this->validate)(-1, min: 0))->toBeFalse();
    expect(($this->validate)(0, min: 0))->toBeTrue();
});

test('max constraint works', function (): void {
    expect(($this->validate)(0.5, max: 1))->toBeTrue();
    expect(($this->validate)(1.5, max: 1))->toBeFalse();
    expect(($this->validate)(1, max: 1))->toBeTrue();
});

test('min and max together', function (): void {
    expect(($this->validate)(0.5, min: 0, max: 1))->toBeTrue();
    expect(($this->validate)(-0.1, min: 0, max: 1))->toBeFalse();
    expect(($this->validate)(1.1, min: 0, max: 1))->toBeFalse();
});

test('high precision values work', function (): void {
    expect(($this->validate)('0.000000001', min: 0))->toBeTrue();
    expect(($this->validate)('999999999.999999999', max: 1000000000))->toBeTrue();
});
