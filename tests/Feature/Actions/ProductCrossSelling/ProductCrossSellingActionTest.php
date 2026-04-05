<?php

use FluxErp\Actions\ProductCrossSelling\CreateProductCrossSelling;
use FluxErp\Actions\ProductCrossSelling\DeleteProductCrossSelling;
use FluxErp\Actions\ProductCrossSelling\UpdateProductCrossSelling;
use FluxErp\Models\Product;

beforeEach(function (): void {
    $this->product = Product::factory()->create();
});

test('create product cross selling', function (): void {
    $cs = CreateProductCrossSelling::make([
        'product_id' => $this->product->getKey(),
        'name' => 'Also bought',
    ])->validate()->execute();

    expect($cs)->name->toBe('Also bought');
});

test('create product cross selling requires product_id and name', function (): void {
    CreateProductCrossSelling::assertValidationErrors([], ['product_id', 'name']);
});

test('update product cross selling', function (): void {
    $cs = CreateProductCrossSelling::make([
        'product_id' => $this->product->getKey(),
        'name' => 'Original',
    ])->validate()->execute();

    $updated = UpdateProductCrossSelling::make([
        'id' => $cs->getKey(),
        'name' => 'Frequently paired',
    ])->validate()->execute();

    expect($updated->name)->toBe('Frequently paired');
});

test('delete product cross selling', function (): void {
    $cs = CreateProductCrossSelling::make([
        'product_id' => $this->product->getKey(),
        'name' => 'Temp',
    ])->validate()->execute();

    expect(DeleteProductCrossSelling::make(['id' => $cs->getKey()])
        ->validate()->execute())->toBeTrue();
});
