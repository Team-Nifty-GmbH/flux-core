<?php

use FluxErp\Livewire\Product\Product;
use FluxErp\Models\Category;
use FluxErp\Models\Product as ProductModel;
use FluxErp\Models\Tenant;
use Livewire\Livewire;

beforeEach(function (): void {
    Tenant::default()->update(['product_variant_inheritance_enabled' => true]);
    Tenant::clearDefaultCache();
});

test('resetField clears overridden_fields on the variant', function (): void {
    $parent = ProductModel::factory()->create(['name' => 'Parent']);
    $variant = ProductModel::factory()->create([
        'parent_id' => $parent->getKey(),
        'overridden_fields' => ['name'],
        'name' => 'override',
    ]);

    Livewire::test(Product::class, ['id' => $variant->getKey()])
        ->call('resetField', 'name')
        ->assertHasNoErrors();

    expect($variant->fresh()->overridden_fields)->toBeNull();
});

test('resetField on non-inheritable field surfaces a validation error', function (): void {
    $parent = ProductModel::factory()->create();
    $variant = ProductModel::factory()->create(['parent_id' => $parent->getKey()]);

    Livewire::test(Product::class, ['id' => $variant->getKey()])
        ->call('resetField', 'product_number')
        ->assertHasErrors(['inheritance']);
});

test('resetRelation deletes own pivot rows for the relation', function (): void {
    $cat = Category::factory()->create([
        'model_type' => morph_alias(ProductModel::class),
    ]);
    $parent = ProductModel::factory()->create();
    $variant = ProductModel::factory()->create(['parent_id' => $parent->getKey()]);
    $variant->ownCategories()->attach([$cat->getKey()]);

    Livewire::test(Product::class, ['id' => $variant->getKey()])
        ->call('resetRelation', 'categories')
        ->assertHasNoErrors();

    expect($variant->ownCategories()->count())->toBe(0);
});

test('resetFieldOnAllVariants clears the field across every variant', function (): void {
    $parent = ProductModel::factory()->create(['name' => 'Parent']);
    ProductModel::factory()->create([
        'parent_id' => $parent->getKey(),
        'overridden_fields' => ['name'],
    ]);
    ProductModel::factory()->create([
        'parent_id' => $parent->getKey(),
        'overridden_fields' => ['name', 'description'],
    ]);

    Livewire::test(Product::class, ['id' => $parent->getKey()])
        ->call('resetFieldOnAllVariants', 'name')
        ->assertHasNoErrors();

    foreach (ProductModel::where('parent_id', $parent->getKey())->get() as $variant) {
        expect($variant->overridden_fields ?? [])->not->toContain('name');
    }
});

test('promoteToStandalone clears was_parent on a parent without active children', function (): void {
    $parent = ProductModel::factory()->create([
        'parent_id' => null,
        'was_parent' => true,
    ]);
    ProductModel::factory()->create([
        'parent_id' => $parent->getKey(),
        'is_active' => false,
    ]);

    Livewire::test(Product::class, ['id' => $parent->getKey()])
        ->call('promoteToStandalone')
        ->assertHasNoErrors();

    expect($parent->fresh()->was_parent)->toBeFalse();
});

test('promoteToStandalone surfaces error when active children still exist', function (): void {
    $parent = ProductModel::factory()->create([
        'parent_id' => null,
        'was_parent' => true,
    ]);
    ProductModel::factory()->create([
        'parent_id' => $parent->getKey(),
        'is_active' => true,
    ]);

    Livewire::test(Product::class, ['id' => $parent->getKey()])
        ->call('promoteToStandalone')
        ->assertHasErrors(['inheritance']);
});
