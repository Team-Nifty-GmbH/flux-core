<?php

use FluxErp\Models\Product;

test('parent.is_variant_parent flips to true when its first child is saved', function (): void {
    $parent = Product::factory()->create(['parent_id' => null]);
    expect($parent->fresh()->is_variant_parent)->toBeFalse();

    Product::factory()->create(['parent_id' => $parent->getKey()]);

    expect($parent->fresh()->is_variant_parent)->toBeTrue();
});

test('is_variant_parent stays true after the last variant is deactivated', function (): void {
    $parent = Product::factory()->create(['parent_id' => null]);
    $variant = Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'is_active' => true,
    ]);

    expect($parent->fresh()->is_variant_parent)->toBeTrue();

    $variant->update(['is_active' => false]);

    expect($parent->fresh()->is_variant_parent)->toBeTrue();
});

test('is_variant_parent stays true after the last variant is deleted', function (): void {
    $parent = Product::factory()->create(['parent_id' => null]);
    $variant = Product::factory()->create(['parent_id' => $parent->getKey()]);

    $variant->delete();

    expect($parent->fresh()->is_variant_parent)->toBeTrue();
});

test('is_variant_parent does not flip on standalone products', function (): void {
    $product = Product::factory()->create(['parent_id' => null]);
    expect($product->fresh()->is_variant_parent)->toBeFalse();
});
