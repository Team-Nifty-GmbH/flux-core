<?php

use FluxErp\Livewire\Portal\Shop\Cart;
use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\VatRate;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Livewire;

test('can add cart item', function (): void {
    /** @var Collection $products */
    $products = Product::factory()
        ->count(2)
        ->hasAttached($this->dbClient, relationship: 'clients')
        ->for(VatRate::factory(), relationship: 'vatRate')
        ->has(
            Price::factory()
                ->for(PriceList::factory()->state(['is_default' => true])),
            relationship: 'prices'
        )
        ->create();

    Livewire::test(Cart::class)
        ->fireEvent('cart:add', $products->first()->id)
        ->assertOk()
        ->assertHasNoErrors()
        ->assertToastNotification(type: 'success')
        ->assertCount('cart.cartItems', 1)
        ->fireEvent('cart:add', [$products->get(1)->id])
        ->assertOk()
        ->assertHasNoErrors()
        ->assertToastNotification(type: 'success')
        ->assertCount('cart.cartItems', 2)
        ->fireEvent('cart:add', ['id' => $products->get(1)->id, 'amount' => 2])
        ->assertOk()
        ->assertHasNoErrors()
        ->assertToastNotification(type: 'success')
        ->assertCount('cart.cartItems', 2)
        ->assertSet('cart.cartItems.1.amount', 3)
        ->assertOk()
        ->assertHasNoErrors()
        ->assertToastNotification(type: 'success');
});

test('renders successfully', function (): void {
    Livewire::test(Cart::class)
        ->assertOk();
});
