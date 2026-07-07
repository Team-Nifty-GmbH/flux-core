<?php

use FluxErp\Jobs\MigrateProductVariantInheritanceJob;
use FluxErp\Models\Category;
use FluxErp\Models\Contact;
use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\ProductProperty;
use FluxErp\Settings\ProductSettings;

beforeEach(function (): void {
    app(ProductSettings::class)->fill(['variant_inheritance_enabled' => true])->save();
});

test('materializes a stale non-overriding child column to the parent value', function (): void {
    // Simulate legacy data written before materialization existed: create with
    // inheritance OFF so the value-diff "saving" hook never marks an override, leaving
    // a stale/divergent child column and overridden_fields untouched (null).
    app(ProductSettings::class)->fill(['variant_inheritance_enabled' => false])->save();
    $parent = Product::factory()->create(['weight_gram' => 100]);
    $variant = Product::factory()->create(['parent_id' => $parent->getKey(), 'weight_gram' => 1]);
    app(ProductSettings::class)->fill(['variant_inheritance_enabled' => true])->save();

    (new MigrateProductVariantInheritanceJob($parent->getKey()))->handle();

    expect((int) DB::table('products')->where('id', $variant->getKey())->value('weight_gram'))
        ->toBe(100);
});

test('leaves an overriding child column untouched', function (): void {
    $parent = Product::factory()->create(['weight_gram' => 100]);
    $variant = Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'weight_gram' => 999,
        'overridden_fields' => ['weight_gram'],
    ]);

    (new MigrateProductVariantInheritanceJob($parent->getKey()))->handle();

    expect((int) DB::table('products')->where('id', $variant->getKey())->value('weight_gram'))
        ->toBe(999);
});

test('processes all parents when no parent_id given', function (): void {
    app(ProductSettings::class)->fill(['variant_inheritance_enabled' => false])->save();
    $parentA = Product::factory()->create(['weight_gram' => 100]);
    $variantA = Product::factory()->create(['parent_id' => $parentA->getKey(), 'weight_gram' => 1]);

    $parentB = Product::factory()->create(['weight_gram' => 200]);
    $variantB = Product::factory()->create(['parent_id' => $parentB->getKey(), 'weight_gram' => 1]);
    app(ProductSettings::class)->fill(['variant_inheritance_enabled' => true])->save();

    (new MigrateProductVariantInheritanceJob())->handle();

    expect((int) DB::table('products')->where('id', $variantA->getKey())->value('weight_gram'))->toBe(100)
        ->and((int) DB::table('products')->where('id', $variantB->getKey())->value('weight_gram'))->toBe(200);
});

test('seeds an inherited price row for a non-owning child', function (): void {
    $list = PriceList::factory()->create();
    $parent = Product::factory()->create();
    Price::factory()->create([
        'product_id' => $parent->getKey(),
        'price_list_id' => $list->getKey(),
        'price' => '100.0000',
    ]);

    $variant = Product::factory()->create(['parent_id' => $parent->getKey()]);

    (new MigrateProductVariantInheritanceJob($parent->getKey()))->handle();

    $row = DB::table('prices')
        ->where('product_id', $variant->getKey())
        ->where('price_list_id', $list->getKey())
        ->whereNull('deleted_at')
        ->first();

    expect($row)->not->toBeNull()
        ->and((bool) $row->is_inherited)->toBeTrue()
        ->and((float) $row->price)->toBe(100.0);
});

test('leaves a child owning its own price untouched', function (): void {
    $list = PriceList::factory()->create();
    $parent = Product::factory()->create();
    Price::factory()->create([
        'product_id' => $parent->getKey(),
        'price_list_id' => $list->getKey(),
        'price' => '100.0000',
    ]);

    $variant = Product::factory()->create(['parent_id' => $parent->getKey()]);
    Price::factory()->create([
        'product_id' => $variant->getKey(),
        'price_list_id' => $list->getKey(),
        'price' => '15.0000',
        'is_inherited' => false,
    ]);

    (new MigrateProductVariantInheritanceJob($parent->getKey()))->handle();

    $row = DB::table('prices')
        ->where('product_id', $variant->getKey())
        ->where('price_list_id', $list->getKey())
        ->whereNull('deleted_at')
        ->first();

    expect((float) $row->price)->toBe(15.0)
        ->and((bool) $row->is_inherited)->toBeFalse();
});

test('seeds inherited category, supplier and product property rows for a non-owning child', function (): void {
    $category = Category::factory()->create(['model_type' => morph_alias(Product::class)]);
    $contact = Contact::factory()->create();
    $property = ProductProperty::factory()->create();

    $parent = Product::factory()->create();
    $parent->ownCategories()->attach([$category->getKey()]);
    $parent->ownSuppliers()->attach([$contact->getKey()]);
    $parent->ownProductProperties()->attach([$property->getKey() => ['value' => 'red']]);

    $variant = Product::factory()->create(['parent_id' => $parent->getKey()]);

    (new MigrateProductVariantInheritanceJob($parent->getKey()))->handle();

    $categoryRow = DB::table('categorizable')
        ->where('categorizable_id', $variant->getKey())
        ->where('categorizable_type', morph_alias(Product::class))
        ->where('category_id', $category->getKey())
        ->first();
    $supplierRow = DB::table('product_supplier')
        ->where('product_id', $variant->getKey())
        ->where('contact_id', $contact->getKey())
        ->first();
    $propertyRow = DB::table('product_product_property')
        ->where('product_id', $variant->getKey())
        ->where('product_property_id', $property->getKey())
        ->first();

    expect($categoryRow)->not->toBeNull()->and((bool) $categoryRow->is_inherited)->toBeTrue()
        ->and($supplierRow)->not->toBeNull()->and((bool) $supplierRow->is_inherited)->toBeTrue()
        ->and($propertyRow)->not->toBeNull()->and((bool) $propertyRow->is_inherited)->toBeTrue()
        ->and($propertyRow->value)->toBe('red');
});

test('is idempotent when run twice', function (): void {
    app(ProductSettings::class)->fill(['variant_inheritance_enabled' => false])->save();
    $parent = Product::factory()->create(['weight_gram' => 100]);
    $variant = Product::factory()->create(['parent_id' => $parent->getKey(), 'weight_gram' => 1]);
    app(ProductSettings::class)->fill(['variant_inheritance_enabled' => true])->save();

    $list = PriceList::factory()->create();
    Price::factory()->create([
        'product_id' => $parent->getKey(),
        'price_list_id' => $list->getKey(),
        'price' => '100.0000',
    ]);

    (new MigrateProductVariantInheritanceJob($parent->getKey()))->handle();
    (new MigrateProductVariantInheritanceJob($parent->getKey()))->handle();

    expect((int) DB::table('products')->where('id', $variant->getKey())->value('weight_gram'))->toBe(100)
        ->and(DB::table('prices')
            ->where('product_id', $variant->getKey())
            ->where('price_list_id', $list->getKey())
            ->whereNull('deleted_at')
            ->count())->toBe(1);
});

test('is a no-op when inheritance is disabled', function (): void {
    app(ProductSettings::class)->fill(['variant_inheritance_enabled' => false])->save();

    $parent = Product::factory()->create(['weight_gram' => 100]);
    $variant = Product::factory()->create(['parent_id' => $parent->getKey(), 'weight_gram' => 1]);

    (new MigrateProductVariantInheritanceJob($parent->getKey()))->handle();

    expect((int) DB::table('products')->where('id', $variant->getKey())->value('weight_gram'))->toBe(1);
});
