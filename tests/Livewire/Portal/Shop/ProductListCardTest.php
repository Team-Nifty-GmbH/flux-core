<?php

use FluxErp\Livewire\Portal\Shop\ProductListCard;
use FluxErp\Models\Product;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $product = Product::factory()
        ->hasAttached(factory: $this->dbClient, relationship: 'clients')
        ->create();

    Livewire::withoutLazyLoading()
        ->test(ProductListCard::class, ['product' => $product->toArray()])
        ->assertOk();
});
