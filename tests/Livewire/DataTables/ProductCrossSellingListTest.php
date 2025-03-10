<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\ProductCrossSellingList;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class ProductCrossSellingListTest extends BaseSetup
{
    public function test_renders_successfully()
    {
        Livewire::test(ProductCrossSellingList::class)
            ->assertStatus(200);
    }
}
