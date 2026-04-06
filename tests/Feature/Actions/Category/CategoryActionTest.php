<?php

use FluxErp\Actions\Category\CreateCategory;
use FluxErp\Actions\Category\DeleteCategory;
use FluxErp\Actions\Category\UpdateCategory;
use FluxErp\Models\Category;

test('create category', function (): void {
    $category = CreateCategory::make([
        'name' => 'Electronics',
        'model_type' => morph_alias(FluxErp\Models\Product::class),
        'sort_number' => 1,
    ])->validate()->execute();

    expect($category)->toBeInstanceOf(Category::class)
        ->name->toBe('Electronics');
});

test('create category requires name and model_type', function (): void {
    CreateCategory::assertValidationErrors([], ['name', 'model_type']);
});

test('create category with parent', function (): void {
    $parent = Category::factory()->create([
        'model_type' => morph_alias(FluxErp\Models\Product::class),
    ]);

    $child = CreateCategory::make([
        'name' => 'Laptops',
        'model_type' => morph_alias(FluxErp\Models\Product::class),
        'parent_id' => $parent->getKey(),
    ])->validate()->execute();

    expect($child->parent_id)->toBe($parent->getKey());
});

test('create category without sort_number succeeds', function (): void {
    $category = CreateCategory::make([
        'name' => 'Auto',
        'model_type' => morph_alias(FluxErp\Models\Product::class),
    ])->validate()->execute();

    expect($category->sort_number)->toBeGreaterThanOrEqual(0);
});

test('update category', function (): void {
    $category = Category::factory()->create([
        'model_type' => morph_alias(FluxErp\Models\Product::class),
    ]);

    $updated = UpdateCategory::make([
        'id' => $category->getKey(),
        'name' => 'Office Supplies',
    ])->validate()->execute();

    expect($updated->name)->toBe('Office Supplies');
});

test('delete category', function (): void {
    $category = Category::factory()->create([
        'model_type' => morph_alias(FluxErp\Models\Product::class),
    ]);

    expect(DeleteCategory::make(['id' => $category->getKey()])
        ->validate()->execute())->toBeTrue();
});
