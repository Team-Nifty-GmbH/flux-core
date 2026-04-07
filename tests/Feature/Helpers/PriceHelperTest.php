<?php

use FluxErp\Helpers\PriceHelper;
use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\VatRate;

beforeEach(function (): void {
    $this->vatRate = VatRate::factory()->create();
    $this->product = Product::factory()->create([
        'vat_rate_id' => $this->vatRate->getKey(),
    ]);
    $this->priceList = PriceList::factory()->create();
});

test('price helper requires product with id', function (): void {
    expect(fn () => PriceHelper::make(new Product()))
        ->toThrow(InvalidArgumentException::class);
});

test('price helper returns null when no price exists', function (): void {
    $helper = PriceHelper::make($this->product)
        ->setPriceList($this->priceList)
        ->useDefault(false);

    expect($helper->price())->toBeNull();
});

test('price helper finds price for product on price list', function (): void {
    Price::factory()->create([
        'product_id' => $this->product->getKey(),
        'price_list_id' => $this->priceList->getKey(),
        'price' => 99.99,
    ]);

    $helper = PriceHelper::make($this->product)
        ->setPriceList($this->priceList);

    $price = $helper->price();

    expect($price)->not->toBeNull()
        ->price->toEqual(99.99);
});

test('price helper falls back to default price list', function (): void {
    $defaultPriceList = PriceList::factory()->create(['is_default' => true]);

    Price::factory()->create([
        'product_id' => $this->product->getKey(),
        'price_list_id' => $defaultPriceList->getKey(),
        'price' => 49.99,
    ]);

    $helper = PriceHelper::make($this->product)
        ->setPriceList($this->priceList);

    $price = $helper->price();

    expect($price)->not->toBeNull();
});

test('price helper getters return correct values', function (): void {
    $helper = PriceHelper::make($this->product)
        ->setPriceList($this->priceList);

    expect($helper->getProduct()->getKey())->toBe($this->product->getKey());
    expect($helper->getPriceList()->getKey())->toBe($this->priceList->getKey());
    expect($helper->getContact())->toBeNull();
});
