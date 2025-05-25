<?php

namespace FluxErp\Tests\Livewire\Portal\Shop;

use FluxErp\Livewire\Portal\Shop\ProductListCard;
use FluxErp\Models\Product;
use FluxErp\Tests\Feature\Web\Portal\PortalSetup;
use Livewire\Livewire;

class ProductListCardTest extends PortalSetup
{
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = Product::factory()
            ->hasAttached(factory: $this->dbClient, relationship: 'clients')
            ->create();
    }

    public function test_renders_successfully(): void
    {
        Livewire::withoutLazyLoading()
            ->test(ProductListCard::class, ['product' => $this->product->toArray()])
            ->assertStatus(200);
    }
}
