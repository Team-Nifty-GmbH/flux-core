<?php

use FluxErp\Livewire\Cart\WatchlistCard;
use FluxErp\Models\Cart;
use FluxErp\Models\PriceList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $cart = Cart::factory()->create([
        'price_list_id' => PriceList::default()->getKey(),
        'authenticatable_type' => $this->user->getMorphClass(),
        'authenticatable_id' => $this->user->getKey(),
        'is_watchlist' => true,
    ]);

    Livewire::test(WatchlistCard::class, ['cart' => $cart])
        ->assertOk();
});
