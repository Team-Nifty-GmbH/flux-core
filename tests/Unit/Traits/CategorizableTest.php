<?php

use FluxErp\Models\Category;
use FluxErp\Models\Product;

test('model can have categories', function (): void {
    $product = Product::factory()->create();
    $category = Category::factory()->create([
        'model_type' => morph_alias(Product::class),
    ]);

    $product->categories()->attach($category);

    expect($product->fresh()->categories)->toHaveCount(1);
});

test('categories can be synced', function (): void {
    $product = Product::factory()->create();
    $cat1 = Category::factory()->create(['model_type' => morph_alias(Product::class)]);
    $cat2 = Category::factory()->create(['model_type' => morph_alias(Product::class)]);

    $product->categories()->sync([$cat1->getKey(), $cat2->getKey()]);

    expect($product->fresh()->categories)->toHaveCount(2);

    $product->categories()->sync([$cat1->getKey()]);

    expect($product->fresh()->categories)->toHaveCount(1);
});
