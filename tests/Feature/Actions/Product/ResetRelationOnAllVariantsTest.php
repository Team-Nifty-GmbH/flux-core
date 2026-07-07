<?php

use FluxErp\Actions\Product\ResetRelationOnAllVariants;
use FluxErp\Models\Category;
use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Settings\ProductSettings;
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

it('re-copies the parents current category as an inherited row on every reset variant', function (): void {
    app(ProductSettings::class)->fill(['variant_inheritance_enabled' => true])->save();

    $catA = Category::factory()->create(['model_type' => morph_alias(Product::class)]);
    $parent = Product::factory()->create();
    $parent->ownCategories()->attach([$catA->getKey()]);
    $variantA = Product::factory()->create(['parent_id' => $parent->getKey()]);
    $variantA->ownCategories()->attach([$catA->getKey()]);
    $variantB = Product::factory()->create(['parent_id' => $parent->getKey()]);
    $variantB->ownCategories()->attach([$catA->getKey()]);

    ResetRelationOnAllVariants::make([
        'parent_id' => $parent->getKey(),
        'relation' => 'categories',
        'key' => $catA->getKey(),
    ])->validate()->execute();

    foreach ([$variantA, $variantB] as $variant) {
        expect($variant->ownCategories()->wherePivot('is_inherited', false)->count())->toBe(0);

        $row = DB::table('categorizable')
            ->where('categorizable_id', $variant->getKey())
            ->where('categorizable_type', morph_alias(Product::class))
            ->where('category_id', $catA->getKey())
            ->first();

        expect($row)->not->toBeNull()
            ->and((bool) $row->is_inherited)->toBeTrue();
    }
});

it('re-copies the parents current price as an inherited row on every reset variant', function (): void {
    app(ProductSettings::class)->fill(['variant_inheritance_enabled' => true])->save();

    $priceList = PriceList::factory()->create(['is_default' => false]);
    $parent = Product::factory()->create();
    Price::factory()->create([
        'product_id' => $parent->getKey(),
        'price_list_id' => $priceList->getKey(),
        'price' => '100.0000',
    ]);
    $variantA = Product::factory()->create(['parent_id' => $parent->getKey()]);
    Price::factory()->create([
        'product_id' => $variantA->getKey(),
        'price_list_id' => $priceList->getKey(),
        'price' => '999.0000',
        'is_inherited' => false,
    ]);
    $variantB = Product::factory()->create(['parent_id' => $parent->getKey()]);
    Price::factory()->create([
        'product_id' => $variantB->getKey(),
        'price_list_id' => $priceList->getKey(),
        'price' => '888.0000',
        'is_inherited' => false,
    ]);

    ResetRelationOnAllVariants::make([
        'parent_id' => $parent->getKey(),
        'relation' => 'prices',
        'key' => $priceList->getKey(),
    ])->validate()->execute();

    foreach ([$variantA, $variantB] as $variant) {
        $ownedRow = DB::table('prices')
            ->where('product_id', $variant->getKey())
            ->where('price_list_id', $priceList->getKey())
            ->where('is_inherited', false)
            ->whereNull('deleted_at')
            ->first();

        expect($ownedRow)->toBeNull();

        $inheritedRow = DB::table('prices')
            ->where('product_id', $variant->getKey())
            ->where('price_list_id', $priceList->getKey())
            ->where('is_inherited', true)
            ->whereNull('deleted_at')
            ->first();

        expect($inheritedRow)->not->toBeNull()
            ->and((float) $inheritedRow->price)->toBe(100.0);
    }
});

it('rejects non-inheritable relations', function (): void {
    $parent = Product::factory()->create();

    ResetRelationOnAllVariants::make([
        'parent_id' => $parent->getKey(),
        'relation' => 'orderPositions',
    ])->validate()->execute();
})->throws(ValidationException::class);
