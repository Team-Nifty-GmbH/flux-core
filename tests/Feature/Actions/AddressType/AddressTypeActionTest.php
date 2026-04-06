<?php

use FluxErp\Actions\AddressType\CreateAddressType;
use FluxErp\Actions\AddressType\DeleteAddressType;
use FluxErp\Actions\AddressType\UpdateAddressType;
use FluxErp\Models\AddressType;

test('create address type', function (): void {
    $type = CreateAddressType::make(['name' => 'Lieferadresse'])
        ->validate()->execute();

    expect($type)->toBeInstanceOf(AddressType::class)
        ->name->toBe('Lieferadresse');
});

test('create address type requires name', function (): void {
    CreateAddressType::assertValidationErrors([], 'name');
});

test('update address type', function (): void {
    $type = AddressType::factory()->create();

    $updated = UpdateAddressType::make([
        'id' => $type->getKey(),
        'name' => 'Rechnungsadresse',
    ])->validate()->execute();

    expect($updated->name)->toBe('Rechnungsadresse');
});

test('delete address type', function (): void {
    $type = AddressType::factory()->create();

    expect(DeleteAddressType::make(['id' => $type->getKey()])
        ->validate()->execute())->toBeTrue();
});
