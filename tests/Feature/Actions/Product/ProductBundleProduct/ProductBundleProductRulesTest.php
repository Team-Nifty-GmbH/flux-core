<?php

use FluxErp\Actions\Product\ProductBundleProduct\CreateProductBundleProduct;
use FluxErp\Actions\Product\ProductBundleProduct\UpdateProductBundleProduct;
use FluxErp\Models\Pivots\BundleProductProduct;
use FluxErp\Models\Product;

test('creating a bundle entry requires the bundled product', function (): void {
    $product = Product::factory()->create();

    CreateProductBundleProduct::assertValidationErrors(
        [
            'product_id' => $product->getKey(),
            'count' => 1,
        ],
        'bundle_product_id'
    );
});

test('creating a bundle entry rejects a product that does not exist', function (): void {
    $product = Product::factory()->create();

    CreateProductBundleProduct::assertValidationErrors(
        [
            'product_id' => $product->getKey(),
            'bundle_product_id' => 999999,
            'count' => 1,
        ],
        'bundle_product_id'
    );
});

test('creating a bundle entry still rejects the same bundled product twice', function (): void {
    $product = Product::factory()->create();
    $bundled = Product::factory()->create();

    CreateProductBundleProduct::make([
        'product_id' => $product->getKey(),
        'bundle_product_id' => $bundled->getKey(),
        'count' => 1,
    ])
        ->validate()
        ->execute();

    CreateProductBundleProduct::assertValidationErrors(
        [
            'product_id' => $product->getKey(),
            'bundle_product_id' => $bundled->getKey(),
            'count' => 1,
        ],
        'bundle_product_id'
    );
});

test('creating a bundle entry accepts a product that exists', function (): void {
    $product = Product::factory()->create();
    $bundled = Product::factory()->create();

    $pivot = CreateProductBundleProduct::make([
        'product_id' => $product->getKey(),
        'bundle_product_id' => $bundled->getKey(),
        'count' => 2,
    ])
        ->validate()
        ->execute();

    expect($pivot)->toBeInstanceOf(BundleProductProduct::class)
        ->and($pivot->bundle_product_id)->toBe($bundled->getKey());
});

test('updating a bundle entry rejects a product that does not exist', function (): void {
    $product = Product::factory()->create();
    $bundled = Product::factory()->create();

    $pivot = CreateProductBundleProduct::make([
        'product_id' => $product->getKey(),
        'bundle_product_id' => $bundled->getKey(),
        'count' => 1,
    ])
        ->validate()
        ->execute();

    UpdateProductBundleProduct::assertValidationErrors(
        [
            'pivot_id' => $pivot->getKey(),
            'product_id' => $product->getKey(),
            'bundle_product_id' => 999999,
        ],
        'bundle_product_id'
    );
});

test('updating a bundle entry keeps its own bundled product', function (): void {
    $product = Product::factory()->create();
    $bundled = Product::factory()->create();

    $pivot = CreateProductBundleProduct::make([
        'product_id' => $product->getKey(),
        'bundle_product_id' => $bundled->getKey(),
        'count' => 1,
    ])
        ->validate()
        ->execute();

    $updated = UpdateProductBundleProduct::make([
        'pivot_id' => $pivot->getKey(),
        'product_id' => $product->getKey(),
        'bundle_product_id' => $bundled->getKey(),
        'count' => 5,
    ])
        ->validate()
        ->execute();

    expect($updated->bundle_product_id)->toBe($bundled->getKey())
        ->and((float) $updated->count)->toBe(5.0);
});
