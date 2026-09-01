<?php

use FluxErp\Actions\VatRate\CreateVatRate;
use FluxErp\Actions\VatRate\DeleteVatRate;
use FluxErp\Actions\VatRate\UpdateVatRate;
use FluxErp\Models\VatRate;

test('create vat rate', function (): void {
    $vat = CreateVatRate::make([
        'name' => 'Reduced',
        'rate_percentage' => 0.07,
    ])->validate()->execute();

    expect($vat)->toBeInstanceOf(VatRate::class)
        ->name->toBe('Reduced')
        ->rate_percentage->toEqual(0.07);
});

test('create vat rate requires name and rate_percentage', function (): void {
    CreateVatRate::assertValidationErrors([], ['name', 'rate_percentage']);
});

test('update vat rate', function (): void {
    $vat = VatRate::factory()->create();

    $updated = UpdateVatRate::make([
        'id' => $vat->getKey(),
        'name' => 'Full Rate',
        'rate_percentage' => 0.19,
    ])->validate()->execute();

    expect($updated)
        ->name->toBe('Full Rate')
        ->rate_percentage->toEqual(0.19);
});

test('delete vat rate', function (): void {
    $vat = VatRate::factory()->create();

    expect(DeleteVatRate::make(['id' => $vat->getKey()])
        ->validate()->execute())->toBeTrue();
});

test('vat rate defaults to usable for purchase and sales', function (): void {
    $vat = CreateVatRate::make([
        'name' => 'Standard',
        'rate_percentage' => 0.19,
    ])
        ->validate()
        ->execute();

    expect($vat->refresh())
        ->is_purchase->toBeTrue()
        ->is_sales->toBeTrue();
});

test('vat rate purchase and sales flags can be set', function (): void {
    $vat = CreateVatRate::make([
        'name' => 'Import VAT',
        'rate_percentage' => 0.19,
        'is_purchase' => true,
        'is_sales' => false,
    ])
        ->validate()
        ->execute();

    expect($vat->refresh())
        ->is_purchase->toBeTrue()
        ->is_sales->toBeFalse();

    $updated = UpdateVatRate::make([
        'id' => $vat->getKey(),
        'name' => $vat->name,
        'rate_percentage' => 0.19,
        'is_sales' => true,
    ])
        ->validate()
        ->execute();

    expect($updated->is_sales)->toBeTrue();
});
