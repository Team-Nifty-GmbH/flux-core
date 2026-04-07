<?php

use FluxErp\Models\Product;

test('children returns child models', function (): void {
    $parent = Product::factory()->create();
    $child = Product::factory()->create(['parent_id' => $parent->getKey()]);

    expect($parent->children)->toHaveCount(1);
    expect($parent->children->first()->getKey())->toBe($child->getKey());
});

test('parent returns parent model', function (): void {
    $parent = Product::factory()->create();
    $child = Product::factory()->create(['parent_id' => $parent->getKey()]);

    expect($child->parent->getKey())->toBe($parent->getKey());
});

test('ancestorKeys returns all ancestor ids', function (): void {
    $grandparent = Product::factory()->create();
    $parent = Product::factory()->create(['parent_id' => $grandparent->getKey()]);
    $child = Product::factory()->create(['parent_id' => $parent->getKey()]);

    $ancestors = $child->ancestorKeys();

    expect($ancestors)->toContain($parent->getKey(), $grandparent->getKey());
});

test('model without parent has no ancestors', function (): void {
    $root = Product::factory()->create();

    $ancestors = $root->ancestorKeys();

    expect($ancestors)->toBeEmpty();
});
