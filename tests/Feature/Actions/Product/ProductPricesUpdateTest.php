<?php

use FluxErp\Actions\Product\ProductPricesUpdate;
use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;

test('creates price in target price list when it has no own price', function (): void {
    $product = Product::factory()->create();

    $sourcePriceList = PriceList::factory()->create(['is_net' => true]);
    $targetPriceList = PriceList::factory()->create(['is_net' => true]);

    Price::factory()->create([
        'product_id' => $product->getKey(),
        'price_list_id' => $sourcePriceList->getKey(),
        'price' => 10,
    ]);

    expect(
        $product->ownPrices()->where('price_list_id', $targetPriceList->getKey())->exists()
    )->toBeFalse();

    ProductPricesUpdate::make([
        'products' => [$product->getKey()],
        'price_list_id' => $targetPriceList->getKey(),
        'base_price_list_id' => $sourcePriceList->getKey(),
        'rounding_method_enum' => 'none',
        'is_percent' => false,
        'alteration' => 0,
    ])->validate()->execute();

    $createdPrice = $product->ownPrices()
        ->where('price_list_id', $targetPriceList->getKey())
        ->first();

    expect($createdPrice)->not->toBeNull()
        ->and((float) $createdPrice->price)->toBe(10.0);
});

test('still updates an existing price in the target price list', function (): void {
    $product = Product::factory()->create();

    $sourcePriceList = PriceList::factory()->create(['is_net' => true]);
    $targetPriceList = PriceList::factory()->create(['is_net' => true]);

    Price::factory()->create([
        'product_id' => $product->getKey(),
        'price_list_id' => $sourcePriceList->getKey(),
        'price' => 10,
    ]);

    $existing = Price::factory()->create([
        'product_id' => $product->getKey(),
        'price_list_id' => $targetPriceList->getKey(),
        'price' => 5,
    ]);

    ProductPricesUpdate::make([
        'products' => [$product->getKey()],
        'price_list_id' => $targetPriceList->getKey(),
        'base_price_list_id' => $sourcePriceList->getKey(),
        'rounding_method_enum' => 'none',
        'is_percent' => false,
        'alteration' => 0,
    ])->validate()->execute();

    expect((float) $existing->fresh()->price)->toBe(10.0)
        ->and(
            $product->ownPrices()->where('price_list_id', $targetPriceList->getKey())->count()
        )->toBe(1);
});
