<?php

use FluxErp\Actions\Warehouse\CreateWarehouse;
use FluxErp\Actions\Warehouse\DeleteWarehouse;
use FluxErp\Actions\Warehouse\UpdateWarehouse;
use FluxErp\Models\Warehouse;

test('create warehouse', function (): void {
    $warehouse = CreateWarehouse::make(['name' => 'Hauptlager'])
        ->validate()->execute();

    expect($warehouse)->toBeInstanceOf(Warehouse::class)
        ->name->toBe('Hauptlager');
});

test('create warehouse requires name', function (): void {
    CreateWarehouse::assertValidationErrors([], 'name');
});

test('update warehouse', function (): void {
    $warehouse = Warehouse::factory()->create();

    $updated = UpdateWarehouse::make([
        'id' => $warehouse->getKey(),
        'name' => 'Nebenlager',
    ])->validate()->execute();

    expect($updated->name)->toBe('Nebenlager');
});

test('delete warehouse', function (): void {
    $warehouse = Warehouse::factory()->create();

    expect(DeleteWarehouse::make(['id' => $warehouse->getKey()])
        ->validate()->execute())->toBeTrue();
});
