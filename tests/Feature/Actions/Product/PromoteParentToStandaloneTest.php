<?php

use FluxErp\Actions\Product\PromoteParentToStandalone;
use FluxErp\Models\Product;
use Illuminate\Validation\ValidationException;

it('clears was_parent on a parent that has no active children', function (): void {
    $parent = Product::factory()->create(['parent_id' => null, 'was_parent' => true]);
    Product::factory()->create(['parent_id' => $parent->getKey(), 'is_active' => false]);

    PromoteParentToStandalone::make(['id' => $parent->getKey()])
        ->validate()
        ->execute();

    expect($parent->fresh()->was_parent)->toBeFalse();
});

it('refuses to promote when active children still exist', function (): void {
    $parent = Product::factory()->create(['parent_id' => null, 'was_parent' => true]);
    Product::factory()->create(['parent_id' => $parent->getKey(), 'is_active' => true]);

    PromoteParentToStandalone::make(['id' => $parent->getKey()])
        ->validate()
        ->execute();
})->throws(ValidationException::class);

it('refuses to promote a non-parent (was_parent already false)', function (): void {
    $product = Product::factory()->create(['parent_id' => null, 'was_parent' => false]);

    PromoteParentToStandalone::make(['id' => $product->getKey()])
        ->validate()
        ->execute();
})->throws(ValidationException::class);
