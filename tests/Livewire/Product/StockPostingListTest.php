<?php

namespace FluxErp\Tests\Livewire\Product;

use FluxErp\Livewire\Product\StockPostingList;
use FluxErp\Models\Client;
use FluxErp\Models\Product;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class StockPostingListTest extends TestCase
{
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $dbClient = Client::factory()->create();

        $this->product = Product::factory()
            ->hasAttached(factory: $dbClient, relationship: 'clients')
            ->create();
    }

    public function test_renders_successfully(): void
    {
        Livewire::test(StockPostingList::class, ['productId' => $this->product->id])
            ->assertStatus(200);
    }
}
