<?php

use FluxErp\Actions\Product\ResetProductRelations;
use FluxErp\Models\Category;
use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

test('removes pivot rows on every variant for the relation', function (): void {
    $category = Category::factory()->create(['model_type' => morph_alias(Product::class)]);
    $parent = Product::factory()->create();
    $variantA = Product::factory()->create(['parent_id' => $parent->getKey()]);
    $variantA->ownCategories()->attach([$category->getKey()]);
    $variantB = Product::factory()->create(['parent_id' => $parent->getKey()]);
    $variantB->ownCategories()->attach([$category->getKey()]);

    $touched = ResetProductRelations::make([
        'parent_id' => $parent->getKey(),
        'relations' => [
            [
                'relation' => 'categories',
                'related_id' => $category->getKey(),
            ],
        ],
    ])
        ->validate()
        ->execute();

    expect($touched)->toBe(2);
});

test('resets a single variant when variant_ids are given', function (): void {
    $category = Category::factory()->create(['model_type' => morph_alias(Product::class)]);
    $parent = Product::factory()->create();
    $variant = Product::factory()->create(['parent_id' => $parent->getKey()]);
    $variant->ownCategories()->attach([$category->getKey()]);
    $sibling = Product::factory()->create(['parent_id' => $parent->getKey()]);
    $sibling->ownCategories()->attach([$category->getKey()]);

    $touched = ResetProductRelations::make([
        'parent_id' => $parent->getKey(),
        'relations' => [
            ['relation' => 'categories'],
        ],
        'variant_ids' => [$variant->getKey()],
    ])
        ->validate()
        ->execute();

    expect($touched)->toBe(1)
        ->and($variant->ownCategories()->count())->toBe(0)
        ->and($sibling->ownCategories()->count())->toBe(1);
});

test('resets multiple relations in one call', function (): void {
    $category = Category::factory()->create(['model_type' => morph_alias(Product::class)]);
    $priceList = PriceList::factory()->create(['is_default' => false]);
    $parent = Product::factory()->create();
    $variant = Product::factory()->create(['parent_id' => $parent->getKey()]);
    $variant->ownCategories()->attach([$category->getKey()]);
    Price::factory()->create([
        'price_list_id' => $priceList->getKey(),
        'product_id' => $variant->getKey(),
        'price' => '999.0000',
        'is_inherited' => false,
    ]);

    $touched = ResetProductRelations::make([
        'parent_id' => $parent->getKey(),
        'relations' => [
            ['relation' => 'categories'],
            ['relation' => 'prices'],
        ],
    ])
        ->validate()
        ->execute();

    expect($touched)->toBe(2)
        ->and($variant->ownCategories()->count())->toBe(0)
        ->and($variant->ownPrices()->count())->toBe(0);
});

test('re-copies the parents current category as an inherited row on every reset variant', function (): void {
    $category = Category::factory()->create(['model_type' => morph_alias(Product::class)]);
    $parent = Product::factory()->create();
    $parent->ownCategories()->attach([$category->getKey()]);
    // Creating the variants materializes inherited copies; take ownership by
    // flipping them, the same way UpdateProduct's pivot sync does.
    $variantA = Product::factory()->create(['parent_id' => $parent->getKey()]);
    $variantA->categories()->updateExistingPivot($category->getKey(), ['is_inherited' => false]);
    $variantB = Product::factory()->create(['parent_id' => $parent->getKey()]);
    $variantB->categories()->updateExistingPivot($category->getKey(), ['is_inherited' => false]);

    ResetProductRelations::make([
        'parent_id' => $parent->getKey(),
        'relations' => [
            [
                'relation' => 'categories',
                'related_id' => $category->getKey(),
            ],
        ],
    ])
        ->validate()
        ->execute();

    foreach ([$variantA, $variantB] as $variant) {
        expect($variant->ownCategories()->count())->toBe(0);

        $row = DB::table('categorizable')
            ->where('category_id', $category->getKey())
            ->where('categorizable_type', morph_alias(Product::class))
            ->where('categorizable_id', $variant->getKey())
            ->first();

        expect($row)->not->toBeNull()
            ->and((bool) $row->is_inherited)->toBeTrue();
    }
});

test('re-copies the parents current price as an inherited row on every reset variant', function (): void {
    $priceList = PriceList::factory()->create(['is_default' => false]);
    $parent = Product::factory()->create();
    Price::factory()->create([
        'price_list_id' => $priceList->getKey(),
        'product_id' => $parent->getKey(),
        'price' => '100.0000',
    ]);
    $variantA = Product::factory()->create(['parent_id' => $parent->getKey()]);
    Price::factory()->create([
        'price_list_id' => $priceList->getKey(),
        'product_id' => $variantA->getKey(),
        'price' => '999.0000',
        'is_inherited' => false,
    ]);
    $variantB = Product::factory()->create(['parent_id' => $parent->getKey()]);
    Price::factory()->create([
        'price_list_id' => $priceList->getKey(),
        'product_id' => $variantB->getKey(),
        'price' => '888.0000',
        'is_inherited' => false,
    ]);

    ResetProductRelations::make([
        'parent_id' => $parent->getKey(),
        'relations' => [
            [
                'relation' => 'prices',
                'related_id' => $priceList->getKey(),
            ],
        ],
    ])
        ->validate()
        ->execute();

    foreach ([$variantA, $variantB] as $variant) {
        $ownedRow = DB::table('prices')
            ->where('price_list_id', $priceList->getKey())
            ->where('product_id', $variant->getKey())
            ->where('is_inherited', false)
            ->whereNull('deleted_at')
            ->first();

        expect($ownedRow)->toBeNull();

        $inheritedRow = DB::table('prices')
            ->where('price_list_id', $priceList->getKey())
            ->where('product_id', $variant->getKey())
            ->where('is_inherited', true)
            ->whereNull('deleted_at')
            ->first();

        expect($inheritedRow)->not->toBeNull()
            ->and((float) $inheritedRow->price)->toBe(100.0);
    }
});

test('rejects non-inheritable relations', function (): void {
    $parent = Product::factory()->create();

    ResetProductRelations::make([
        'parent_id' => $parent->getKey(),
        'relations' => [
            ['relation' => 'orderPositions'],
        ],
    ])
        ->validate()
        ->execute();
})->throws(ValidationException::class);

test('rejects when parent_id refers to a variant', function (): void {
    $top = Product::factory()->create();
    $variant = Product::factory()->create(['parent_id' => $top->getKey()]);

    ResetProductRelations::make([
        'parent_id' => $variant->getKey(),
        'relations' => [
            ['relation' => 'categories'],
        ],
    ])
        ->validate()
        ->execute();
})->throws(ValidationException::class);
