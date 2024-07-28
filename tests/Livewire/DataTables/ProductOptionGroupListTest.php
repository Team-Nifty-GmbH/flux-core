<?php

namespace Tests\Feature\Livewire\DataTables;

use FluxErp\Livewire\DataTables\ProductOptionGroupList;
use Livewire\Livewire;
use Tests\TestCase;

class ProductOptionGroupListTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(ProductOptionGroupList::class)
            ->assertStatus(200);
    }
}
