<?php

use FluxErp\Actions\Location\CreateLocation;
use FluxErp\Actions\Location\DeleteLocation;
use FluxErp\Actions\Location\UpdateLocation;

test('create location', function (): void {
    $location = CreateLocation::make(['name' => 'Office Berlin'])
        ->validate()->execute();

    expect($location)->name->toBe('Office Berlin');
});

test('create location requires name', function (): void {
    CreateLocation::assertValidationErrors([], 'name');
});

test('update location', function (): void {
    $location = CreateLocation::make(['name' => 'Office Berlin'])
        ->validate()->execute();

    $updated = UpdateLocation::make([
        'id' => $location->getKey(),
        'name' => 'Office Munich',
    ])->validate()->execute();

    expect($updated->name)->toBe('Office Munich');
});

test('delete location', function (): void {
    $location = CreateLocation::make(['name' => 'Temp'])
        ->validate()->execute();

    expect(DeleteLocation::make(['id' => $location->getKey()])
        ->validate()->execute())->toBeTrue();
});
