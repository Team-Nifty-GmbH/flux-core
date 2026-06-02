<?php

use FluxErp\Actions\Product\Variant\CreateVariants;
use FluxErp\Models\Category;
use FluxErp\Models\Product;
use FluxErp\Models\ProductOption;
use FluxErp\Models\ProductOptionGroup;
use FluxErp\Models\Tenant;
use FluxErp\Models\VatRate;

it('does not attach inherited categories to a new variant when inheritance is enabled', function (): void {
    app(FluxErp\Settings\ProductSettings::class)->fill(['variant_inheritance_enabled' => true])->save();

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

    expect($variant->ownCategories()->count())->toBe(0);
    expect($variant->categories->pluck('id')->all())->toBe([$cat->getKey()]);
});

it('still attaches parent categories to a new variant when inheritance is disabled', function (): void {
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
