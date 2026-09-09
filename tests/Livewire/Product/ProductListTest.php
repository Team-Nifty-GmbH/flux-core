<?php

use FluxErp\Actions\Product\ProductPricesUpdate;
use FluxErp\Livewire\Product\ProductList;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use Illuminate\Support\Facades\Bus;
use Livewire\Livewire;

test('can add products to cart', function (): void {
    $products = Product::factory()->count(3)->create();

    Livewire::test(ProductList::class)
        ->set('selected', [$products->first()->id])
        ->call('addSelectedToCart')
        ->assertDispatched('cart:add', [$products->first()->id])
        ->assertSet('selected', []);
});

test('renders successfully', function (): void {
    Livewire::test(ProductList::class)
        ->assertOk();
});

test('update prices dispatches a monitored job instead of running synchronously', function (): void {
    Bus::fake([ProductPricesUpdate::class]);

    $products = Product::factory()->count(2)->create();
    $priceList = PriceList::factory()->create();

    Livewire::test(ProductList::class)
        ->set('selected', $products->pluck('id')->toArray())
        ->set('productPricesUpdate.price_list_id', $priceList->getKey())
        ->set('productPricesUpdate.base_price_list_id', $priceList->getKey())
        ->set('productPricesUpdate.is_percent', false)
        ->set('productPricesUpdate.alteration', 0)
        ->set('productPricesUpdate.rounding_method_enum', 'none')
        ->call('updatePrices')
        ->assertHasNoErrors()
        ->assertSet('selected', []);

    Bus::assertDispatched(
        ProductPricesUpdate::class,
        fn (ProductPricesUpdate $action): bool => $action->getData('products') === $products->pluck('id')->toArray()
            && $action->getData('price_list_id') === $priceList->getKey()
            && $action->getData('base_price_list_id') === $priceList->getKey()
            && $action->getData('is_percent') === false
    );
});
