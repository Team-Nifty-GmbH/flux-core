<?php

namespace FluxErp\Tests\Livewire\Portal\Shop;

use FluxErp\Livewire\Portal\Shop\Cart;
use FluxErp\Models\Currency;
use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\VatRate;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Livewire;

class CartTest extends BaseSetup
{
    public function setUp(): void
    {
        parent::setUp();

        PriceList::factory()->create([
            'is_default' => true,
        ]);
        Currency::factory()->create([
            'is_default' => true,
        ]);
    }

    public function test_renders_successfully()
    {
        Livewire::test(Cart::class)
            ->assertStatus(200);
    }

    public function test_can_add_cart_item()
    {
        /** @var Collection $product */
        $product = Product::factory()
            ->count(3)
            ->hasAttached($this->dbClient, relationship: 'clients')
            ->for(VatRate::factory(), relationship: 'vatRate')
            ->has(
                Price::factory()
                    ->for(PriceList::factory()->state(['is_default' => true])),
                relationship: 'prices'
            )
            ->create();

        Livewire::test(Cart::class)
            ->fireEvent('cart:add', $product->first()->id)
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertWireuiNotification(icon: 'success')
            ->assertCount('cart.cartItems', 1)
            ->fireEvent('cart:add', [$product->get(1)->id])
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertWireuiNotification(icon: 'success')
            ->assertCount('cart.cartItems', 2)
            ->fireEvent('cart:add', ['id' => $product->get(1)->id, 'amount' => 2])
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertWireuiNotification(icon: 'success')
            ->assertCount('cart.cartItems', 2)
            ->assertSet('cart.cartItems.1.amount', 3)
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertWireuiNotification(icon: 'success');
    }
}
