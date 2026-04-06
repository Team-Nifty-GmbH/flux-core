<?php

use FluxErp\Actions\Currency\CreateCurrency;
use FluxErp\Actions\Currency\DeleteCurrency;
use FluxErp\Actions\Currency\UpdateCurrency;
use FluxErp\Models\Currency;

test('create currency', function (): void {
    $currency = CreateCurrency::make([
        'name' => 'US Dollar',
        'iso' => 'USD',
        'symbol' => '$',
    ])->validate()->execute();

    expect($currency)
        ->toBeInstanceOf(Currency::class)
        ->name->toBe('US Dollar')
        ->iso->toBe('USD')
        ->symbol->toBe('$');
});

test('create currency requires name iso symbol', function (): void {
    CreateCurrency::assertValidationErrors([], ['name', 'iso', 'symbol']);
});

test('update currency', function (): void {
    $currency = Currency::factory()->create();

    $updated = UpdateCurrency::make([
        'id' => $currency->getKey(),
        'name' => 'Updated Currency',
    ])->validate()->execute();

    expect($updated->name)->toBe('Updated Currency');
});

test('delete currency', function (): void {
    $currency = Currency::factory()->create();

    $result = DeleteCurrency::make([
        'id' => $currency->getKey(),
    ])->validate()->execute();

    expect($result)->toBeTrue();
});
