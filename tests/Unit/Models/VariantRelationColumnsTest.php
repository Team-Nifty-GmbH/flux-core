<?php

use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use Illuminate\Support\Facades\Schema;

it('has is_inherited boolean column on prices table', function (): void {
    expect(Schema::hasColumn('prices', 'is_inherited'))->toBeTrue();
});

it('casts price is_inherited to a real boolean from the database column', function (): void {
    $price = Price::factory()->create([
        'product_id' => Product::factory()->create()->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
        'is_inherited' => true,
    ]);

    expect($price->fresh()->is_inherited)->toBeTrue();

    $default = Price::factory()->create([
        'product_id' => Product::factory()->create()->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
    ]);

    expect($default->fresh()->is_inherited)->toBeFalse();
});

it('has is_inherited boolean column on categorizable table', function (): void {
    expect(Schema::hasColumn('categorizable', 'is_inherited'))->toBeTrue();
});

it('has is_inherited boolean column on product_supplier table', function (): void {
    expect(Schema::hasColumn('product_supplier', 'is_inherited'))->toBeTrue();
});

it('has is_inherited boolean column on product_product_property table', function (): void {
    expect(Schema::hasColumn('product_product_property', 'is_inherited'))->toBeTrue();
});
