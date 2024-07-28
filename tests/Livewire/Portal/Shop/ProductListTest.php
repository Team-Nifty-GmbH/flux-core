<?php

namespace Tests\Feature\Livewire\Portal\Shop;

use FluxErp\Livewire\Portal\Shop\ProductList;
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
