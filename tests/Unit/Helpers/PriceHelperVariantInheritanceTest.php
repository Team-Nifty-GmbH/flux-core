<?php

use FluxErp\Helpers\PriceHelper;
use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\VatRate;

beforeEach(function (): void {
    $this->vatRate = VatRate::default() ?? VatRate::factory()->create(['is_default' => true]);
});

test('variant inherits parent price when no own price for the price list', function (): void {
    $list = PriceList::factory()->create(['is_default' => false]);
    $parent = Product::factory()->create(['vat_rate_id' => $this->vatRate->getKey()]);
    Price::factory()->create([
        'product_id' => $parent->getKey(),
        'price_list_id' => $list->getKey(),
        'price' => '100.0000',
    ]);

    $variant = Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'vat_rate_id' => $this->vatRate->getKey(),
    ]);

    $price = PriceHelper::make($variant)
        ->setPriceList($list)
        ->useDefault(false)
        ->price();

    expect($price)->not->toBeNull()
        ->and((float) $price->price)->toBe(100.0);
});

test('variant own price wins over parent price', function (): void {
    $list = PriceList::factory()->create(['is_default' => false]);
    $parent = Product::factory()->create(['vat_rate_id' => $this->vatRate->getKey()]);
    Price::factory()->create([
        'product_id' => $parent->getKey(),
        'price_list_id' => $list->getKey(),
        'price' => '100.0000',
    ]);

    $variant = Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'vat_rate_id' => $this->vatRate->getKey(),
    ]);
    Price::factory()->create([
        'product_id' => $variant->getKey(),
        'price_list_id' => $list->getKey(),
        'price' => '50.0000',
    ]);

    $price = PriceHelper::make($variant)
        ->setPriceList($list)
        ->useDefault(false)
        ->price();

    expect((float) $price->price)->toBe(50.0);
});

test('variant returns null when neither variant nor parent has price', function (): void {
    $list = PriceList::factory()->create(['is_default' => false]);
    $parent = Product::factory()->create(['vat_rate_id' => $this->vatRate->getKey()]);
    $variant = Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'vat_rate_id' => $this->vatRate->getKey(),
    ]);

    $price = PriceHelper::make($variant)
        ->setPriceList($list)
        ->useDefault(false)
        ->price();

    expect($price)->toBeNull();
});

test('variant does not inherit parent price when inheritance is disabled', function (): void {
    app(FluxErp\Settings\ProductSettings::class)->fill(['variant_inheritance_enabled' => false])->save();

    $list = PriceList::factory()->create(['is_default' => false]);
    $parent = Product::factory()->create(['vat_rate_id' => $this->vatRate->getKey()]);
    Price::factory()->create([
        'product_id' => $parent->getKey(),
        'price_list_id' => $list->getKey(),
        'price' => '100.0000',
    ]);

    $variant = Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'vat_rate_id' => $this->vatRate->getKey(),
    ]);

    $price = PriceHelper::make($variant)
        ->setPriceList($list)
        ->useDefault(false)
        ->price();

    expect($price)->toBeNull();
});

test('variant inherits parent default price when useDefault is on', function (): void {
    $defaultList = PriceList::default();
    $parent = Product::factory()->create(['vat_rate_id' => $this->vatRate->getKey()]);
    Price::factory()->create([
        'product_id' => $parent->getKey(),
        'price_list_id' => $defaultList->getKey(),
        'price' => '77.0000',
    ]);

    $variant = Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'vat_rate_id' => $this->vatRate->getKey(),
    ]);

    $unrelatedList = PriceList::factory()->create(['is_default' => false]);

    $price = PriceHelper::make($variant)
        ->setPriceList($unrelatedList)
        ->price();

    expect($price)->not->toBeNull()
        ->and((float) $price->price)->toBe(77.0);
});
