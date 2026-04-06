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
