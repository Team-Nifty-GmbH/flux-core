<?php

use FluxErp\Actions\ProductOptionGroup\CreateProductOptionGroup;
use FluxErp\Actions\ProductOptionGroup\DeleteProductOptionGroup;
use FluxErp\Actions\ProductOptionGroup\UpdateProductOptionGroup;
use FluxErp\Models\ProductOptionGroup;

test('create product option group', function (): void {
    $group = CreateProductOptionGroup::make(['name' => 'Color'])
        ->validate()->execute();

    expect($group)->toBeInstanceOf(ProductOptionGroup::class)
        ->name->toBe('Color');
});

test('create product option group requires name', function (): void {
    CreateProductOptionGroup::assertValidationErrors([], 'name');
});

test('update product option group', function (): void {
    $group = ProductOptionGroup::factory()->create();

    $updated = UpdateProductOptionGroup::make([
        'id' => $group->getKey(),
        'name' => 'Size',
    ])->validate()->execute();

    expect($updated->name)->toBe('Size');
});

test('delete product option group', function (): void {
    $group = ProductOptionGroup::factory()->create();

    expect(DeleteProductOptionGroup::make(['id' => $group->getKey()])
        ->validate()->execute())->toBeTrue();
});
