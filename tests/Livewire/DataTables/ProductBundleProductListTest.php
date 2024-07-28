<?php

namespace Tests\Feature\Livewire\DataTables;

use FluxErp\Livewire\DataTables\ProductBundleProductList;
use Livewire\Livewire;
use Tests\TestCase;

class ProductBundleProductListTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(ProductBundleProductList::class)
            ->assertStatus(200);
    }
}
