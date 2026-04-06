<?php

use FluxErp\Actions\ProductPropertyGroup\CreateProductPropertyGroup;
use FluxErp\Actions\ProductPropertyGroup\DeleteProductPropertyGroup;
use FluxErp\Actions\ProductPropertyGroup\UpdateProductPropertyGroup;

test('create product property group', function (): void {
    $group = CreateProductPropertyGroup::make(['name' => 'Physical'])
        ->validate()->execute();

    expect($group)->name->toBe('Physical');
});

test('create product property group requires name', function (): void {
    CreateProductPropertyGroup::assertValidationErrors([], 'name');
});

test('update product property group', function (): void {
    $group = CreateProductPropertyGroup::make(['name' => 'Original'])
        ->validate()->execute();

    $updated = UpdateProductPropertyGroup::make([
        'id' => $group->getKey(),
        'name' => 'Technical',
    ])->validate()->execute();

    expect($updated->name)->toBe('Technical');
});

test('delete product property group', function (): void {
    $group = CreateProductPropertyGroup::make(['name' => 'Temp'])
        ->validate()->execute();

    expect(DeleteProductPropertyGroup::make(['id' => $group->getKey()])
        ->validate()->execute())->toBeTrue();
});
