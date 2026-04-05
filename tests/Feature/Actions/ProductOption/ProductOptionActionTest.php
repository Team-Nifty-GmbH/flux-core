<?php

use FluxErp\Actions\ProductOption\CreateProductOption;
use FluxErp\Actions\ProductOption\DeleteProductOption;
use FluxErp\Actions\ProductOption\UpdateProductOption;
use FluxErp\Models\ProductOption;
use FluxErp\Models\ProductOptionGroup;

beforeEach(function (): void {
    $this->group = ProductOptionGroup::factory()->create();
});

test('create product option', function (): void {
    $option = CreateProductOption::make([
        'product_option_group_id' => $this->group->getKey(),
        'name' => 'Red',
    ])->validate()->execute();

    expect($option)->toBeInstanceOf(ProductOption::class)
        ->name->toBe('Red');
});

test('create product option requires group and name', function (): void {
    CreateProductOption::assertValidationErrors([], ['product_option_group_id', 'name']);
});

test('update product option', function (): void {
    $option = ProductOption::factory()->create([
        'product_option_group_id' => $this->group->getKey(),
    ]);

    $updated = UpdateProductOption::make([
        'id' => $option->getKey(),
        'name' => 'Blue',
    ])->validate()->execute();

    expect($updated->name)->toBe('Blue');
});

test('delete product option', function (): void {
    $option = ProductOption::factory()->create([
        'product_option_group_id' => $this->group->getKey(),
    ]);

    expect(DeleteProductOption::make(['id' => $option->getKey()])
        ->validate()->execute())->toBeTrue();
});
