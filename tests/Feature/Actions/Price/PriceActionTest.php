<?php

use FluxErp\Actions\Price\CreatePrice;
use FluxErp\Actions\Price\DeletePrice;
use FluxErp\Actions\Price\UpdatePrice;
use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;

beforeEach(function (): void {
    $this->product = Product::factory()->create();
    $this->priceList = PriceList::factory()->create();
});

test('create price', function (): void {
    $price = CreatePrice::make([
        'product_id' => $this->product->getKey(),
        'price_list_id' => $this->priceList->getKey(),
        'price' => 99.99,
    ])->validate()->execute();

    expect($price)->toBeInstanceOf(Price::class)
        ->price->toEqual(99.99);
});

test('create price requires product price_list and price', function (): void {
    CreatePrice::assertValidationErrors([], ['product_id', 'price_list_id', 'price']);
});

test('update price', function (): void {
    $price = Price::factory()->create([
        'product_id' => $this->product->getKey(),
        'price_list_id' => $this->priceList->getKey(),
    ]);

    $updated = UpdatePrice::make([
        'id' => $price->getKey(),
        'price' => 149.99,
    ])->validate()->execute();

    expect($updated->price)->toEqual(149.99);
});

test('delete price', function (): void {
    $price = Price::factory()->create([
        'product_id' => $this->product->getKey(),
        'price_list_id' => $this->priceList->getKey(),
    ]);

    expect(DeletePrice::make(['id' => $price->getKey()])
        ->validate()->execute())->toBeTrue();
});
