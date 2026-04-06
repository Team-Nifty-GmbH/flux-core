<?php

use FluxErp\Rules\Iban;
use Illuminate\Support\Facades\Validator;

function validateIban(string $value): bool
{
    return Validator::make(['iban' => $value], ['iban' => app(Iban::class)])->passes();
}

test('valid german iban passes', function (): void {
    expect(validateIban('DE89370400440532013000'))->toBeTrue();
});

test('valid austrian iban passes', function (): void {
    expect(validateIban('AT611904300234573201'))->toBeTrue();
});

test('valid british iban passes', function (): void {
    expect(validateIban('GB29NWBK60161331926819'))->toBeTrue();
});

test('iban with spaces passes', function (): void {
    expect(validateIban('DE89 3704 0044 0532 0130 00'))->toBeTrue();
});

test('lowercase iban passes', function (): void {
    expect(validateIban('de89370400440532013000'))->toBeTrue();
});

test('invalid checksum fails', function (): void {
    expect(validateIban('DE00370400440532013000'))->toBeFalse();
});

test('wrong length fails', function (): void {
    expect(validateIban('DE8937040044053201300'))->toBeFalse();
});

test('unknown country code fails', function (): void {
    expect(validateIban('XX89370400440532013000'))->toBeFalse();
});

test('random string fails', function (): void {
    expect(validateIban('not-an-iban'))->toBeFalse();
});
