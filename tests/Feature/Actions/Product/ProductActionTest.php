<?php

use FluxErp\Actions\Product\CreateProduct;
use FluxErp\Actions\Product\DeleteProduct;
use FluxErp\Actions\Product\UpdateProduct;
use FluxErp\Models\Product;
use FluxErp\Models\ProductProperty;
use FluxErp\Models\VatRate;

test('create product with defaults', function (): void {
    $product = CreateProduct::make([
        'name' => 'Test Widget',
    ])->validate()->execute();

    expect($product)
        ->toBeInstanceOf(Product::class)
        ->name->toBe('Test Widget')
        ->vat_rate_id->not->toBeNull();
});

test('create product requires name', function (): void {
    CreateProduct::assertValidationErrors([], 'name');
});

test('update product', function (): void {
    $product = Product::factory()->create();

    $updated = UpdateProduct::make([
        'id' => $product->getKey(),
        'name' => 'Updated Widget',
    ])->validate()->execute();

    expect($updated->name)->toBe('Updated Widget');
});

test('update product detects parent-child cycle', function (): void {
    $parent = Product::factory()->create();
    $child = Product::factory()->create(['parent_id' => $parent->getKey()]);

    UpdateProduct::assertValidationErrors([
        'id' => $parent->getKey(),
        'parent_id' => $child->getKey(),
    ], 'parent_id');
});

test('delete product', function (): void {
    $product = Product::factory()->create();

    $result = DeleteProduct::make(['id' => $product->getKey()])
        ->validate()->execute();

    expect($result)->toBeTrue();
});

test('delete product with children fails', function (): void {
    $parent = Product::factory()->create();
    Product::factory()->create(['parent_id' => $parent->getKey()]);

    expect(fn () => DeleteProduct::make(['id' => $parent->getKey()])
        ->validate()->execute()
    )->toThrow(Illuminate\Validation\ValidationException::class);
});

test('create product rejects a purchase-only vat rate', function (): void {
    $purchaseOnlyVatRate = VatRate::factory()->create([
        'is_purchase' => true,
        'is_sales' => false,
    ]);

    CreateProduct::assertValidationErrors([
        'name' => 'Test Widget',
        'vat_rate_id' => $purchaseOnlyVatRate->getKey(),
    ], 'vat_rate_id');
});

test('update product keeps its properties when none are passed', function (): void {
    $product = Product::factory()->create();
    $property = ProductProperty::factory()->create();
    $product->productProperties()->attach($property->getKey(), ['value' => 'kept']);

    UpdateProduct::make([
        'id' => $product->getKey(),
        'name' => 'Updated Widget',
    ])->validate()->execute();

    expect($product->productProperties()->pluck('id')->all())->toBe([$property->getKey()]);
});

test('update product syncs its properties when they are passed', function (): void {
    $product = Product::factory()->create();
    $stale = ProductProperty::factory()->create();
    $wanted = ProductProperty::factory()->create();
    $product->productProperties()->attach($stale->getKey(), ['value' => 'gone']);

    UpdateProduct::make([
        'id' => $product->getKey(),
        'product_properties' => [['id' => $wanted->getKey(), 'value' => 'set']],
    ])->validate()->execute();

    expect($product->productProperties()->pluck('id')->all())->toBe([$wanted->getKey()]);
});
