<?php

namespace FluxErp\Tests\Livewire\Portal\Shop;

use FluxErp\Livewire\Portal\Shop\ProductDetail;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class ProductDetailTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(ProductDetail::class)
            ->assertStatus(200);
    }
}
