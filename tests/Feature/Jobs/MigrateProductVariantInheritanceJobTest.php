<?php

use FluxErp\Jobs\MigrateProductVariantInheritanceJob;
use FluxErp\Models\Category;
use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;

beforeEach(function (): void {
    app(FluxErp\Settings\ProductSettings::class)->fill(['variant_inheritance_enabled' => true])->save();
});

/**
 * Build the inheritable column overrides needed to make a variant match a parent
 * regardless of factory-generated random defaults. Returns an array suitable for
 * spreading into `Product::factory()->create([...])`.
 */
function variantInheritableState(Product $parent, array $overrides = []): array
{
    $parentRaw = $parent->getRawOriginal();
    $aligned = collect($parent->getInheritableFields())
        ->filter(fn (string $field) => array_key_exists($field, $parentRaw))
        ->mapWithKeys(fn (string $field) => [$field => $parentRaw[$field]])
        ->all();

    return array_merge($aligned, $overrides);
}

it('marks variant fields as overridden when they differ from parent', function (): void {
    $parent = Product::factory()->create(['name' => 'Same', 'description' => 'shared']);
    $variant = Product::factory()->create(variantInheritableState($parent, [
        'parent_id' => $parent->getKey(),
        'description' => 'different',
        'overridden_fields' => null,
    ]));

    (new MigrateProductVariantInheritanceJob($parent->getKey()))->handle();

    expect($variant->fresh()->overridden_fields)->toBe(['description']);
});

it('clears overridden_fields when variant equals parent (idempotent)', function (): void {
    $parent = Product::factory()->create(['name' => 'Same']);
    $variant = Product::factory()->create(variantInheritableState($parent, [
        'parent_id' => $parent->getKey(),
        'overridden_fields' => ['name'],
    ]));

    (new MigrateProductVariantInheritanceJob($parent->getKey()))->handle();

    expect($variant->fresh()->overridden_fields)->toBeNull();
});

it('processes all parents when no parent_id given', function (): void {
    $parentA = Product::factory()->create(['name' => 'A']);
    $variantA = Product::factory()->create(variantInheritableState($parentA, [
        'parent_id' => $parentA->getKey(),
    ]));

    $parentB = Product::factory()->create(['name' => 'B']);
    $variantB = Product::factory()->create(variantInheritableState($parentB, [
        'parent_id' => $parentB->getKey(),
        'name' => 'B-other',
    ]));

    (new MigrateProductVariantInheritanceJob(null))->handle();

    expect($variantA->fresh()->overridden_fields)->toBeNull();
    expect($variantB->fresh()->overridden_fields)->toBe(['name']);
});

it('removes redundant variant prices that match parent', function (): void {
    $listA = PriceList::factory()->create();
    $parent = Product::factory()->create();
    Price::factory()->create([
        'product_id' => $parent->getKey(),
        'price_list_id' => $listA->getKey(),
        'price' => 10,
    ]);

    $variant = Product::factory()->create(['parent_id' => $parent->getKey()]);
    Price::factory()->create([
        'product_id' => $variant->getKey(),
        'price_list_id' => $listA->getKey(),
        'price' => 10,
    ]);

    (new MigrateProductVariantInheritanceJob($parent->getKey()))->handle();

    expect($variant->ownPrices()->count())->toBe(0);
});

it('keeps variant prices that differ from parent', function (): void {
    $listA = PriceList::factory()->create();
    $parent = Product::factory()->create();
    Price::factory()->create([
        'product_id' => $parent->getKey(),
        'price_list_id' => $listA->getKey(),
        'price' => 10,
    ]);

    $variant = Product::factory()->create(['parent_id' => $parent->getKey()]);
    Price::factory()->create([
        'product_id' => $variant->getKey(),
        'price_list_id' => $listA->getKey(),
        'price' => 15,
    ]);

    (new MigrateProductVariantInheritanceJob($parent->getKey()))->handle();

    expect($variant->ownPrices()->count())->toBe(1);
    expect($variant->ownPrices->first()->price)->toEqual(15);
});

it('removes redundant variant categories that match parent', function (): void {
    $cat = Category::factory()->create(['model_type' => morph_alias(Product::class)]);
    $parent = Product::factory()->create();
    $parent->ownCategories()->attach([$cat->getKey()]);

    $variant = Product::factory()->create(['parent_id' => $parent->getKey()]);
    $variant->ownCategories()->attach([$cat->getKey()]);

    (new MigrateProductVariantInheritanceJob($parent->getKey()))->handle();

    expect($variant->ownCategories()->count())->toBe(0);
});
