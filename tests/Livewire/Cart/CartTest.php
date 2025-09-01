<?php

uses(FluxErp\Tests\Livewire\BaseSetup::class);
use FluxErp\Database\Factories\CartFactory;
use FluxErp\Livewire\Cart\Cart;
use FluxErp\Models\Cart as CartModel;
use FluxErp\Models\CartItem;
use FluxErp\Models\Currency;
use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\VatRate;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function (): void {
    PriceList::factory()->create([
        'is_default' => true,
    ]);
    Currency::factory()->create([
        'is_default' => true,
    ]);

    $this->vatRate = VatRate::factory()->create();
});

test('can delete cart items', function (): void {
    $cart = createFilledCartFactory()
        ->create([
            'authenticatable_type' => $this->user->getMorphClass(),
            'authenticatable_id' => $this->user->id,
            'price_list_id' => PriceList::default()->id,
            'is_watchlist' => false,
        ]);

    Livewire::actingAs($this->user)
        ->withoutLazyLoading()
        ->test(Cart::class)
        ->assertCount('cart.cartItems', 3)
        ->call('remove', $cart->cartItems->first()->getKey())
        ->assertHasNoErrors()
        ->assertCount('cart.cartItems', 2);
});

test('can load watchlist', function (): void {
    $watchList = createFilledCartFactory()
        ->create([
            'authenticatable_type' => $this->user->getMorphClass(),
            'authenticatable_id' => $this->user->id,
            'price_list_id' => PriceList::default()->id,
            'is_watchlist' => true,
        ]);

    Livewire::actingAs($this->user)
        ->withoutLazyLoading()
        ->test(Cart::class)
        ->set('loadWatchlist', $watchList->id)
        ->assertSet('loadWatchlist', null)
        ->assertStatus(200)
        ->assertCount('cart.cartItems', 3)
        ->assertToastNotification(type: 'success');
});

test('can save cart to watchlist', function (): void {
    createFilledCartFactory()
        ->create([
            'authenticatable_type' => $this->user->getMorphClass(),
            'authenticatable_id' => $this->user->id,
            'price_list_id' => PriceList::default()->id,
            'is_watchlist' => false,
        ]);

    Livewire::actingAs($this->user)
        ->withoutLazyLoading()
        ->test(Cart::class)
        ->assertCount('cart.cartItems', 3)
        ->set('watchlistName', $watchListName = Str::uuid())
        ->call('saveToWatchlist')
        ->assertHasNoErrors()
        ->assertReturned(true)
        ->assertToastNotification(type: 'success');

    $this->assertDatabaseHas('carts', [
        'authenticatable_type' => $this->user->getMorphClass(),
        'authenticatable_id' => $this->user->id,
        'price_list_id' => PriceList::default()->id,
        'is_watchlist' => true,
        'name' => $watchListName,
    ]);
});

test('renders successfully', function (): void {
    Livewire::withoutLazyLoading()
        ->test(Cart::class)
        ->assertStatus(200);
});

function createFilledCartFactory(): CartFactory
{
    return CartModel::factory()
        ->has(
            CartItem::factory()
                ->count(3)
                ->set('vat_rate_id', $this->vatRate->id)
                ->afterCreating(function (CartItem $cartItem): void {
                    $cartItem->product_id = Product::factory(['vat_rate_id' => $this->vatRate->id])
                        ->has(Price::factory()->set('price_list_id', PriceList::default()->id))
                        ->create()
                        ->id;
                    $cartItem->save();
                })
        );
}
