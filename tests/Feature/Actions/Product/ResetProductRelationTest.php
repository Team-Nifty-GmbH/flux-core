<?php

use FluxErp\Actions\Product\ResetProductRelation;
use FluxErp\Models\Category;
use FluxErp\Models\Product;
use Illuminate\Validation\ValidationException;

it('removes pivot rows for an inheritable relation', function (): void {
    $catA = Category::factory()->create(['model_type' => morph_alias(Product::class)]);
    $parent = Product::factory()->create();
    $variant = Product::factory()->create(['parent_id' => $parent->getKey()]);
    $variant->ownCategories()->attach([$catA->getKey()]);

    ResetProductRelation::make([
        'id' => $variant->getKey(),
        'relation' => 'categories',
    ])->validate()->execute();

    expect($variant->ownCategories()->count())->toBe(0);
});

it('rejects non-inheritable relations', function (): void {
    $parent = Product::factory()->create();
    $variant = Product::factory()->create(['parent_id' => $parent->getKey()]);

    ResetProductRelation::make([
        'id' => $variant->getKey(),
        'relation' => 'orderPositions',
    ])->validate()->execute();
})->throws(ValidationException::class);
