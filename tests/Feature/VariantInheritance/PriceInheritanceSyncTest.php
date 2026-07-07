<?php

use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Settings\ProductSettings;

beforeEach(function (): void {
    app(ProductSettings::class)->fill(['variant_inheritance_enabled' => true])->save();

    $this->list = PriceList::factory()->create(['is_default' => false]);
    $this->parent = Product::factory()->create();
    $this->child1 = Product::factory()->create(['parent_id' => $this->parent->getKey()]);
    $this->child2 = Product::factory()->create(['parent_id' => $this->parent->getKey()]);
});

test('parent price creation propagates an inherited copy to each child', function (): void {
    $price = Price::factory()->create([
        'product_id' => $this->parent->getKey(),
        'price_list_id' => $this->list->getKey(),
        'price' => '100.0000',
    ]);

    foreach ([$this->child1, $this->child2] as $child) {
        $row = DB::table('prices')
            ->where('product_id', $child->getKey())
            ->where('price_list_id', $this->list->getKey())
            ->whereNull('deleted_at')
            ->first();

        expect($row)->not->toBeNull()
            ->and((bool) $row->is_inherited)->toBeTrue()
            ->and((float) $row->price)->toBe(100.0);
    }
});

test('parent price update refreshes inherited copies but leaves owned child prices alone', function (): void {
    // child2 owns its own price for this list before the parent price ever exists,
    // so it must never receive/be overwritten by an inherited copy.
    Price::factory()->create([
        'product_id' => $this->child2->getKey(),
        'price_list_id' => $this->list->getKey(),
        'price' => '42.0000',
        'is_inherited' => false,
    ]);

    $price = Price::factory()->create([
        'product_id' => $this->parent->getKey(),
        'price_list_id' => $this->list->getKey(),
        'price' => '100.0000',
    ]);

    $price->update(['price' => '250.0000']);

    $child1Row = DB::table('prices')
        ->where('product_id', $this->child1->getKey())
        ->where('price_list_id', $this->list->getKey())
        ->whereNull('deleted_at')
        ->first();

    $child2Row = DB::table('prices')
        ->where('product_id', $this->child2->getKey())
        ->where('price_list_id', $this->list->getKey())
        ->whereNull('deleted_at')
        ->first();

    expect((float) $child1Row->price)->toBe(250.0)
        ->and((bool) $child1Row->is_inherited)->toBeTrue()
        ->and((float) $child2Row->price)->toBe(42.0)
        ->and((bool) $child2Row->is_inherited)->toBeFalse();
});

test('parent price deletion soft-deletes inherited copies', function (): void {
    $price = Price::factory()->create([
        'product_id' => $this->parent->getKey(),
        'price_list_id' => $this->list->getKey(),
        'price' => '100.0000',
    ]);

    $price->delete();

    foreach ([$this->child1, $this->child2] as $child) {
        $row = DB::table('prices')
            ->where('product_id', $child->getKey())
            ->where('price_list_id', $this->list->getKey())
            ->whereNull('deleted_at')
            ->first();

        expect($row)->toBeNull();

        $trashedRow = DB::table('prices')
            ->where('product_id', $child->getKey())
            ->where('price_list_id', $this->list->getKey())
            ->whereNotNull('deleted_at')
            ->first();

        expect($trashedRow)->not->toBeNull();
    }
});

test('parent price deletion leaves a child\'s owned price untouched', function (): void {
    Price::factory()->create([
        'product_id' => $this->child2->getKey(),
        'price_list_id' => $this->list->getKey(),
        'price' => '42.0000',
        'is_inherited' => false,
    ]);

    $price = Price::factory()->create([
        'product_id' => $this->parent->getKey(),
        'price_list_id' => $this->list->getKey(),
        'price' => '100.0000',
    ]);

    $price->delete();

    $child2Row = DB::table('prices')
        ->where('product_id', $this->child2->getKey())
        ->where('price_list_id', $this->list->getKey())
        ->whereNull('deleted_at')
        ->first();

    expect($child2Row)->not->toBeNull()
        ->and((bool) $child2Row->is_inherited)->toBeFalse()
        ->and((float) $child2Row->price)->toBe(42.0);
});
