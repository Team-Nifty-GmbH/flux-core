<?php

namespace FluxErp\Tests\Livewire\Product;

use FluxErp\Livewire\Product\Product as ProductView;
use FluxErp\Models\Client;
use FluxErp\Models\Currency;
use FluxErp\Models\Product;
use FluxErp\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class ProductTest extends TestCase
{
    use DatabaseTransactions;

    private Product $product;

    public function setUp(): void
    {
        parent::setUp();

        $dbClient = Client::factory()->create();

        $this->product = Product::factory()->create([
            'client_id' => $dbClient->id,
        ]);

        Currency::factory()->create(['is_default' => true]);
    }

    public function test_renders_successfully()
    {
        Livewire::test(ProductView::class, ['id' => $this->product->id])
            ->assertStatus(200);
    }
}
