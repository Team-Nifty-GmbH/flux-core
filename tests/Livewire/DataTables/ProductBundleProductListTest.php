<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\ProductBundleProductList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class ProductBundleProductListTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(ProductBundleProductList::class)
            ->assertStatus(200);
    }
}
