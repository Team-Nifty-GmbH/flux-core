<?php

use FluxErp\Livewire\Cart\Watchlists;
use FluxErp\Models\Cart;
use FluxErp\Models\CartItem;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\VatRate;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Watchlists::class)
        ->assertOk();
});

test('each watchlist card renders its own products', function (): void {
    $vatRate = VatRate::factory()->create();

    $productAlpha = Product::factory()->create([
        'name' => 'UniqueProductAlpha',
        'vat_rate_id' => $vatRate->getKey(),
    ]);

    $productBeta = Product::factory()->create([
        'name' => 'UniqueProductBeta',
        'vat_rate_id' => $vatRate->getKey(),
    ]);

    $priceListId = PriceList::default()->getKey();

    $watchlist1 = Cart::factory()->create([
        'price_list_id' => $priceListId,
        'authenticatable_type' => $this->user->getMorphClass(),
        'authenticatable_id' => $this->user->getKey(),
        'name' => 'Watchlist Alpha',
        'is_watchlist' => true,
    ]);

    CartItem::factory()->create([
        'cart_id' => $watchlist1->getKey(),
        'product_id' => $productAlpha->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'name' => 'UniqueProductAlpha',
    ]);

    $watchlist2 = Cart::factory()->create([
        'price_list_id' => $priceListId,
        'authenticatable_type' => $this->user->getMorphClass(),
        'authenticatable_id' => $this->user->getKey(),
        'name' => 'Watchlist Beta',
        'is_watchlist' => true,
    ]);

    CartItem::factory()->create([
        'cart_id' => $watchlist2->getKey(),
        'product_id' => $productBeta->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'name' => 'UniqueProductBeta',
    ]);

    $html = Livewire::withoutLazyLoading()
        ->actingAs($this->user)
        ->test(Watchlists::class)
        ->assertSee('Watchlist Alpha')
        ->assertSee('Watchlist Beta')
        ->html();

    $visibleHtml = preg_replace('/wire:snapshot="[^"]*"/', '', $html);

    expect($visibleHtml)->toContain('UniqueProductAlpha');
    expect($visibleHtml)->toContain('UniqueProductBeta');
});
