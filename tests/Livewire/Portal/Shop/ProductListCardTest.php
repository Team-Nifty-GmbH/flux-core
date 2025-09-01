<?php

uses(FluxErp\Tests\Feature\Web\Portal\PortalSetup::class);
use FluxErp\Livewire\Portal\Shop\ProductListCard;
use FluxErp\Models\Product;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->product = Product::factory()
        ->hasAttached(factory: $this->dbClient, relationship: 'clients')
        ->create();
});

test('renders successfully', function (): void {
    Livewire::withoutLazyLoading()
        ->test(ProductListCard::class, ['product' => $this->product->toArray()])
        ->assertStatus(200);
});
