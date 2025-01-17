<?php

namespace Tests\Feature\Livewire\Cart;

use FluxErp\Database\Factories\CartFactory;
use FluxErp\Livewire\Cart\Cart;
use FluxErp\Models\Cart as CartModel;
use FluxErp\Models\CartItem;
use FluxErp\Models\Currency;
use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\VatRate;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Support\Str;
use Livewire\Livewire;

class CartTest extends BaseSetup
{
    private VatRate $vatRate;

    protected function setUp(): void
    {
        parent::setUp();

        PriceList::factory()->create([
            'is_default' => true,
        ]);
        Currency::factory()->create([
            'is_default' => true,
        ]);

        $this->vatRate = VatRate::factory()->create();
    }

    public function test_renders_successfully()
    {
        Livewire::test(Cart::class)
            ->assertStatus(200);
    }

    public function test_can_load_watchlist()
    {
        $watchList = $this->createFilledCartFactory()
            ->create([
                'authenticatable_type' => $this->user->getMorphClass(),
                'authenticatable_id' => $this->user->id,
                'price_list_id' => PriceList::default()->id,
                'is_watchlist' => true,
            ]);

        Livewire::actingAs($this->user)
            ->test(Cart::class)
            ->set('loadWatchlist', $watchList->id)
            ->assertSet('loadWatchlist', null)
            ->assertStatus(200)
            ->assertCount('cart.cartItems', 3)
            ->assertWireuiNotification(icon: 'success');
    }

    public function test_can_save_cart_to_watchlist()
    {
        $this->createFilledCartFactory()
            ->create([
                'authenticatable_type' => $this->user->getMorphClass(),
                'authenticatable_id' => $this->user->id,
                'price_list_id' => PriceList::default()->id,
                'is_watchlist' => false,
            ]);

        Livewire::actingAs($this->user)
            ->test(Cart::class)
            ->assertCount('cart.cartItems', 3)
            ->set('watchlistName', $watchListName = Str::uuid())
            ->call('saveToWatchlist')
            ->assertHasNoErrors()
            ->assertReturned(true)
            ->assertWireuiNotification(icon: 'success');

        $this->assertDatabaseHas('carts', [
            'authenticatable_type' => $this->user->getMorphClass(),
            'authenticatable_id' => $this->user->id,
            'price_list_id' => PriceList::default()->id,
            'is_watchlist' => true,
            'name' => $watchListName,
        ]);
    }

    private function createFilledCartFactory(): CartFactory
    {
        return CartModel::factory()
            ->has(
                CartItem::factory()
                    ->count(3)
                    ->set('vat_rate_id', $this->vatRate->id)
                    ->afterCreating(function (CartItem $cartItem) {
                        $cartItem->product_id = Product::factory(['vat_rate_id' => $this->vatRate->id])
                            ->has(Price::factory()->set('price_list_id', PriceList::default()->id))
                            ->create()
                            ->id;
                        $cartItem->save();
                    })
            );
    }
}
