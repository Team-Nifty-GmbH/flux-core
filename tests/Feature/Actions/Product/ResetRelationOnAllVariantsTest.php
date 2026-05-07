<?php

use FluxErp\Actions\Product\ResetRelationOnAllVariants;
use FluxErp\Models\Category;
use FluxErp\Models\Product;
use Illuminate\Validation\ValidationException;

it('removes pivot rows on every variant for the relation', function (): void {
    $catA = Category::factory()->create(['model_type' => morph_alias(Product::class)]);
    $parent = Product::factory()->create();
    $variantA = Product::factory()->create(['parent_id' => $parent->getKey()]);
    $variantA->ownCategories()->attach([$catA->getKey()]);
    $variantB = Product::factory()->create(['parent_id' => $parent->getKey()]);
    $variantB->ownCategories()->attach([$catA->getKey()]);

    $touched = ResetRelationOnAllVariants::make([
        'parent_id' => $parent->getKey(),
        'relation' => 'categories',
        'key' => $catA->getKey(),
    ])->validate()->execute();

    expect($touched)->toBe(2);
});

it('rejects non-inheritable relations', function (): void {
    $parent = Product::factory()->create();

    ResetRelationOnAllVariants::make([
        'parent_id' => $parent->getKey(),
        'relation' => 'orderPositions',
    ])->validate()->execute();
})->throws(ValidationException::class);
