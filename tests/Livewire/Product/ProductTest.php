<?php

namespace FluxErp\Tests\Livewire\Product;

use FluxErp\Livewire\Product\Product;
use FluxErp\Models\Currency;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class ProductTest extends BaseSetup
{
    use DatabaseTransactions;

    private \FluxErp\Models\Product $product;

    public function setUp(): void
    {
        parent::setUp();

        $this->product = \FluxErp\Models\Product::factory()->create([
            'client_id' => $this->dbClient->id,
        ]);

        Currency::factory()->create(['is_default' => true]);
    }

    public function test_renders_successfully()
    {
        Livewire::test(Product::class, ['id' => $this->product->id])
            ->assertStatus(200);
    }
}
