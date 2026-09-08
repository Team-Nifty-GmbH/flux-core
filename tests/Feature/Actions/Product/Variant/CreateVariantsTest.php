<?php

use FluxErp\Actions\Product\Variant\CreateVariants;
use FluxErp\Models\Category;
use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\ProductOption;
use FluxErp\Models\ProductOptionGroup;
use FluxErp\Models\Tenant;
use FluxErp\Models\VatRate;

test('materializes an inherited category copy on a new variant when inheritance is enabled', function (): void {
    $cat = Category::factory()->create(['model_type' => morph_alias(Product::class)]);
    $parent = Product::factory()->create(['vat_rate_id' => VatRate::default()?->getKey()]);
    $parent->tenants()->attach(Tenant::default()->getKey());
    $parent->ownCategories()->attach([$cat->getKey()]);

    $optionGroup = ProductOptionGroup::factory()->create();
    $option = ProductOption::factory()->create(['product_option_group_id' => $optionGroup->getKey()]);

    CreateVariants::make([
        'parent_id' => $parent->getKey(),
        'vat_rate_id' => VatRate::default()?->getKey(),
        'product_options' => [[$option->getKey()]],
    ])->validate()->execute();

    $variant = $parent->children()->first();

    // The materialized copy is inherited, not owned.
    expect($variant->ownCategories()->count())->toBe(0)
        ->and($variant->categories()->count())->toBe(1);
    expect((bool) DB::table('categorizable')
        ->where('categorizable_id', $variant->getKey())
        ->where('categorizable_type', morph_alias(Product::class))
        ->where('category_id', $cat->getKey())
        ->value('is_inherited'))->toBeTrue();
    expect($variant->categories->pluck('id')->all())->toBe([$cat->getKey()]);
});

test('still attaches parent categories to a new variant when inheritance is disabled', function (): void {
    app(FluxErp\Settings\ProductSettings::class)->fill(['variant_inheritance_enabled' => false])->save();

    $cat = Category::factory()->create(['model_type' => morph_alias(Product::class)]);
    $parent = Product::factory()->create(['vat_rate_id' => VatRate::default()?->getKey()]);
    $parent->tenants()->attach(Tenant::default()->getKey());
    $parent->ownCategories()->attach([$cat->getKey()]);

    $optionGroup = ProductOptionGroup::factory()->create();
    $option = ProductOption::factory()->create(['product_option_group_id' => $optionGroup->getKey()]);

    CreateVariants::make([
        'parent_id' => $parent->getKey(),
        'vat_rate_id' => VatRate::default()?->getKey(),
        'product_options' => [[$option->getKey()]],
    ])->validate()->execute();

    $variant = $parent->children()->first();

    expect($variant->ownCategories()->pluck('id')->all())->toBe([$cat->getKey()]);
});

test('seeds a new variant with the parent\'s materialized scalar values, real relation copies and an overridden computed name', function (): void {
    $parent = Product::factory()->create([
        'vat_rate_id' => VatRate::default()?->getKey(),
        'weight_gram' => 500,
    ]);
    $parent->tenants()->attach(Tenant::default()->getKey());
    $priceList = PriceList::factory()->create(['is_default' => false]);
    Price::factory()->create([
        'product_id' => $parent->getKey(),
        'price_list_id' => $priceList->getKey(),
        'price' => 12.5,
    ]);

    $optionGroup = ProductOptionGroup::factory()->create();
    $option = ProductOption::factory()->create([
        'product_option_group_id' => $optionGroup->getKey(),
        'name' => 'Red',
    ]);

    CreateVariants::make([
        'parent_id' => $parent->getKey(),
        'vat_rate_id' => VatRate::default()?->getKey(),
        'product_options' => [[$option->getKey()]],
    ])->validate()->execute();

    $variant = $parent->children()->first();
    $expectedName = $parent->name . ' - Red';

    // (a) the variant's own name column holds the computed variant name and is marked overridden
    expect(DB::table('products')->where('id', $variant->getKey())->value('name'))->toBe($expectedName);
    expect($variant->fresh()->overridden_fields)->toContain('name');

    // (b) scalar inheritable fields are copied from the parent
    expect((float) $variant->weight_gram)->toBe(500.0);

    // (c) inherited relation rows exist for the variant with is_inherited = true
    expect(DB::table('prices')
        ->where('product_id', $variant->getKey())
        ->where('price_list_id', $priceList->getKey())
        ->where('is_inherited', true)
        ->exists())->toBeTrue();

    // (d) reading $variant->name returns the variant's own computed name, not the parent's
    // (the pre-existing name-shadowing regression)
    expect($variant->fresh()->name)->toBe($expectedName)
        ->and($variant->fresh()->name)->not->toBe($parent->name);
});
