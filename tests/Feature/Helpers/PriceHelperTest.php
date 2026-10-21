<?php

use FluxErp\Helpers\PriceHelper;
use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\VatRate;
use Illuminate\Support\Facades\DB;

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

test('price helper reuses the loaded product and price list for the found price', function (): void {
    Price::factory()->create([
        'product_id' => $this->product->getKey(),
        'price_list_id' => $this->priceList->getKey(),
        'price' => 99.99,
    ]);

    $helper = PriceHelper::make($this->product)
        ->setPriceList($this->priceList);

    DB::enableQueryLog();
    $price = $helper->price();
    $tables = collect(DB::getQueryLog())
        ->pluck('query')
        ->filter(fn (string $query): bool => preg_match('/from `(products|vat_rates)`/', $query) === 1);
    DB::disableQueryLog();

    expect($tables)->toBeEmpty()
        ->and($price->priceList->is($this->priceList))->toBeTrue()
        ->and($price->product->is($this->product))->toBeTrue();
});

test('price helper keeps gross and net of a net price list', function (): void {
    $vatRate = VatRate::factory()->create(['rate_percentage' => 0.19]);
    $product = Product::factory()->create(['vat_rate_id' => $vatRate->getKey()]);
    $priceList = PriceList::factory()->create(['is_net' => true]);

    Price::factory()->create([
        'product_id' => $product->getKey(),
        'price_list_id' => $priceList->getKey(),
        'price' => 100,
    ]);

    $price = PriceHelper::make($product)
        ->setPriceList($priceList)
        ->price();

    expect($price->is_net)->toBeTrue()
        ->and(bccomp($price->net, '100', 2))->toBe(0)
        ->and(bccomp($price->gross, '119', 2))->toBe(0);
});

test('price helper keeps discount and root price of an inherited price list', function (): void {
    $parentPriceList = PriceList::factory()->create(['is_net' => true]);
    $childPriceList = PriceList::factory()->create([
        'parent_id' => $parentPriceList->getKey(),
        'is_net' => true,
    ]);

    Price::factory()->create([
        'product_id' => $this->product->getKey(),
        'price_list_id' => $parentPriceList->getKey(),
        'price' => 200,
    ]);

    $childPriceList->discount()->create([
        'discount' => 0.25,
        'is_percentage' => true,
    ]);

    $price = PriceHelper::make($this->product)
        ->setPriceList($childPriceList->refresh())
        ->price();

    expect(bccomp($price->price, '150', 2))->toBe(0)
        ->and(bccomp($price->base_price_flat, '200', 2))->toBe(0)
        ->and(bccomp($price->discount_flat, '50', 2))->toBe(0)
        ->and($parentPriceList->refresh()->is_net)->toBeTrue();
});
