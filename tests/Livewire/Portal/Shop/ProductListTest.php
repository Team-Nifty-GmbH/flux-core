<?php

namespace FluxErp\Tests\Livewire\Portal\Shop;

use FluxErp\Livewire\Portal\Shop\ProductList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class ProductListTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(ProductList::class)
            ->assertStatus(200);
    }
}
