<?php

use FluxErp\Actions\Product\ResetProductRelation;
use FluxErp\Models\Category;
use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Settings\ProductSettings;
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

it('re-copies the parents current category as an inherited row on reset', function (): void {
    app(ProductSettings::class)->fill(['variant_inheritance_enabled' => true])->save();

    $catA = Category::factory()->create(['model_type' => morph_alias(Product::class)]);
    $parent = Product::factory()->create();
    $parent->ownCategories()->attach([$catA->getKey()]);
    $variant = Product::factory()->create(['parent_id' => $parent->getKey()]);
    $variant->ownCategories()->attach([$catA->getKey()]);

    ResetProductRelation::make([
        'id' => $variant->getKey(),
        'relation' => 'categories',
        'key' => $catA->getKey(),
    ])->validate()->execute();

    expect($variant->ownCategories()->wherePivot('is_inherited', false)->count())->toBe(0);

    $row = DB::table('categorizable')
        ->where('categorizable_id', $variant->getKey())
        ->where('categorizable_type', morph_alias(Product::class))
        ->where('category_id', $catA->getKey())
        ->first();

    expect($row)->not->toBeNull()
        ->and((bool) $row->is_inherited)->toBeTrue();
});

it('re-copies the parents current price as an inherited row on reset', function (): void {
    app(ProductSettings::class)->fill(['variant_inheritance_enabled' => true])->save();

    $priceList = PriceList::factory()->create(['is_default' => false]);
    $parent = Product::factory()->create();
    Price::factory()->create([
        'product_id' => $parent->getKey(),
        'price_list_id' => $priceList->getKey(),
        'price' => '100.0000',
    ]);
    $variant = Product::factory()->create(['parent_id' => $parent->getKey()]);
    Price::factory()->create([
        'product_id' => $variant->getKey(),
        'price_list_id' => $priceList->getKey(),
        'price' => '999.0000',
        'is_inherited' => false,
    ]);

    ResetProductRelation::make([
        'id' => $variant->getKey(),
        'relation' => 'prices',
        'key' => $priceList->getKey(),
    ])->validate()->execute();

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
});

it('rejects non-inheritable relations', function (): void {
    $parent = Product::factory()->create();
    $variant = Product::factory()->create(['parent_id' => $parent->getKey()]);

    ResetProductRelation::make([
        'id' => $variant->getKey(),
        'relation' => 'orderPositions',
    ])->validate()->execute();
})->throws(ValidationException::class);
