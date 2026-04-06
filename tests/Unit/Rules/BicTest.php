<?php

use FluxErp\Rules\Bic;
use Illuminate\Support\Facades\Validator;

function validateBic(string $value): bool
{
    return Validator::make(['bic' => $value], ['bic' => app(Bic::class)])->passes();
}

test('valid 8 char bic passes', function (): void {
    expect(validateBic('COBADEFF'))->toBeTrue();
});

test('valid 11 char bic passes', function (): void {
    expect(validateBic('COBADEFFXXX'))->toBeTrue();
});

test('lowercase bic passes', function (): void {
    expect(validateBic('cobadeff'))->toBeTrue();
});

test('wrong length fails', function (): void {
    expect(validateBic('COBADE'))->toBeFalse();
    expect(validateBic('COBADEFFXXXX'))->toBeFalse();
});

test('invalid characters fail', function (): void {
    expect(validateBic('12345678'))->toBeFalse();
});
