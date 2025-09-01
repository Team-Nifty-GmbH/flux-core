<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Product\StockPostingList;
use FluxErp\Models\Client;
use FluxErp\Models\Product;
use Livewire\Livewire;

beforeEach(function (): void {
    $dbClient = Client::factory()->create();

    $this->product = Product::factory()
        ->hasAttached(factory: $dbClient, relationship: 'clients')
        ->create();
});

test('renders successfully', function (): void {
    Livewire::test(StockPostingList::class, ['productId' => $this->product->id])
        ->assertStatus(200);
});
