<?php

uses(FluxErp\Tests\Livewire\PortalBaseSetup::class);
use FluxErp\Livewire\Portal\Shop\Cart;
use FluxErp\Models\Currency;
use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\VatRate;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Livewire;

beforeEach(function (): void {
    PriceList::factory()->create([
        'is_default' => true,
    ]);
    Currency::factory()->create([
        'is_default' => true,
    ]);
});

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
        ->assertStatus(200)
        ->assertHasNoErrors()
        ->assertToastNotification(type: 'success')
        ->assertCount('cart.cartItems', 1)
        ->fireEvent('cart:add', [$products->get(1)->id])
        ->assertStatus(200)
        ->assertHasNoErrors()
        ->assertToastNotification(type: 'success')
        ->assertCount('cart.cartItems', 2)
        ->fireEvent('cart:add', ['id' => $products->get(1)->id, 'amount' => 2])
        ->assertStatus(200)
        ->assertHasNoErrors()
        ->assertToastNotification(type: 'success')
        ->assertCount('cart.cartItems', 2)
        ->assertSet('cart.cartItems.1.amount', 3)
        ->assertStatus(200)
        ->assertHasNoErrors()
        ->assertToastNotification(type: 'success');
});

test('renders successfully', function (): void {
    Livewire::test(Cart::class)
        ->assertStatus(200);
});
