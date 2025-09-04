<?php

use FluxErp\Livewire\Product\StockPostingList;
use FluxErp\Models\Product;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $product = Product::factory()
        ->hasAttached(factory: $this->dbClient, relationship: 'clients')
        ->create();

    Livewire::test(StockPostingList::class, ['productId' => $product->id])
        ->assertOk();
});
