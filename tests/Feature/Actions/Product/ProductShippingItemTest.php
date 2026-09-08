<?php

use FluxErp\Actions\Product\CreateProduct;
use FluxErp\Models\Product;

test('a product can be marked as the one that carries the shipping costs', function (): void {
    $product = CreateProduct::make([
        'name' => 'Shipping',
        'product_number' => 'SHIP-1',
        'is_shipping_item' => true,
    ])
        ->validate()
        ->execute();

    expect(Product::query()->whereKey($product->getKey())->value('is_shipping_item'))->toBeTrue();
});

test('a product is not a shipping item by default', function (): void {
    $product = CreateProduct::make([
        'name' => 'Regular',
        'product_number' => 'REG-1',
    ])
        ->validate()
        ->execute();

    expect($product->refresh()->is_shipping_item)->toBeFalse();
});
