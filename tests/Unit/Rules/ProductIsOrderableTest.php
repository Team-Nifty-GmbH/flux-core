<?php

use FluxErp\Models\Product;
use FluxErp\Rules\ProductIsOrderable;
use Illuminate\Support\Facades\Validator;

it('passes for a standalone product (no children, was_parent=false)', function (): void {
    $product = Product::factory()->create(['parent_id' => null, 'was_parent' => false]);

    $validator = Validator::make(
        ['product_id' => $product->getKey()],
        ['product_id' => [new ProductIsOrderable()]]
    );

    expect($validator->fails())->toBeFalse();
});

it('passes for a variant', function (): void {
    $parent = Product::factory()->create();
    $variant = Product::factory()->create(['parent_id' => $parent->getKey()]);

    $validator = Validator::make(
        ['product_id' => $variant->getKey()],
        ['product_id' => [new ProductIsOrderable()]]
    );

    expect($validator->fails())->toBeFalse();
});

it('fails for an active parent (has active children)', function (): void {
    $parent = Product::factory()->create(['parent_id' => null]);
    Product::factory()->create(['parent_id' => $parent->getKey(), 'is_active' => true]);

    $validator = Validator::make(
        ['product_id' => $parent->getKey()],
        ['product_id' => [new ProductIsOrderable()]]
    );

    expect($validator->fails())->toBeTrue();
});

it('fails for an orphaned parent (was_parent=true, no active children)', function (): void {
    $parent = Product::factory()->create(['parent_id' => null, 'was_parent' => true]);
    Product::factory()->create(['parent_id' => $parent->getKey(), 'is_active' => false]);

    $validator = Validator::make(
        ['product_id' => $parent->getKey()],
        ['product_id' => [new ProductIsOrderable()]]
    );

    expect($validator->fails())->toBeTrue();
});

it('passes when value is null (existence checked elsewhere)', function (): void {
    $validator = Validator::make(
        ['product_id' => null],
        ['product_id' => [new ProductIsOrderable()]]
    );

    expect($validator->fails())->toBeFalse();
});
