<?php

namespace Tests\Feature\Livewire\Portal\Shop;

use FluxErp\Livewire\Portal\Shop\ProductDetail;
use Livewire\Livewire;
use Tests\TestCase;

class ProductDetailTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(ProductDetail::class)
            ->assertStatus(200);
    }
}
