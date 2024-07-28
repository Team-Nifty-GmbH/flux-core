<?php

namespace Tests\Feature\Livewire\Product;

use FluxErp\Livewire\Product\ProductList;
use Livewire\Livewire;
use Tests\TestCase;

class ProductListTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(ProductList::class)
            ->assertStatus(200);
    }
}
