<?php

use FluxErp\Rules\Bic;
use Illuminate\Support\Facades\Validator;

beforeEach(function (): void {
    $this->validate = fn (string $value) => Validator::make(
        ['bic' => $value],
        ['bic' => app(Bic::class)]
    )->passes();
});

test('valid 8 char bic passes', function (): void {
    expect(($this->validate)('COBADEFF'))->toBeTrue();
});

test('valid 11 char bic passes', function (): void {
    expect(($this->validate)('COBADEFFXXX'))->toBeTrue();
});

test('lowercase bic passes', function (): void {
    expect(($this->validate)('cobadeff'))->toBeTrue();
});

test('wrong length fails', function (): void {
    expect(($this->validate)('COBADE'))->toBeFalse();
    expect(($this->validate)('COBADEFFXXXX'))->toBeFalse();
});

test('invalid characters fail', function (): void {
    expect(($this->validate)('12345678'))->toBeFalse();
});
