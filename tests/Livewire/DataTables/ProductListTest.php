<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\ProductList;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class ProductListTest extends BaseSetup
{
    public function test_renders_successfully()
    {
        Livewire::test(ProductList::class)
            ->assertStatus(200);
    }
}
