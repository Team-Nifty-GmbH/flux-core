<?php

use FluxErp\Actions\Product\PromoteParentToStandalone;
use FluxErp\Models\Product;
use Illuminate\Validation\ValidationException;

test('clears is_variant_parent on a parent that has no active children', function (): void {
    $parent = Product::factory()->create(['parent_id' => null, 'is_variant_parent' => true]);
    Product::factory()->create(['parent_id' => $parent->getKey(), 'is_active' => false]);

    PromoteParentToStandalone::make(['id' => $parent->getKey()])
        ->validate()
        ->execute();

    expect($parent->fresh()->is_variant_parent)->toBeFalse();
});

test('refuses to promote when active children still exist', function (): void {
    $parent = Product::factory()->create(['parent_id' => null, 'is_variant_parent' => true]);
    Product::factory()->create(['parent_id' => $parent->getKey(), 'is_active' => true]);

    PromoteParentToStandalone::make(['id' => $parent->getKey()])
        ->validate()
        ->execute();
})->throws(ValidationException::class);

test('refuses to promote a non-parent (is_variant_parent already false)', function (): void {
    $product = Product::factory()->create(['parent_id' => null, 'is_variant_parent' => false]);

    PromoteParentToStandalone::make(['id' => $product->getKey()])
        ->validate()
        ->execute();
})->throws(ValidationException::class);
