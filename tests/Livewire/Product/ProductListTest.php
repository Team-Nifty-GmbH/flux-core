<?php

namespace FluxErp\Tests\Livewire\Product;

use FluxErp\Livewire\Product\ProductList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class ProductListTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(ProductList::class)
            ->assertStatus(200);
    }
}
