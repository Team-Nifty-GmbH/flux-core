<?php

namespace Tests\Feature\Livewire\Product;

use FluxErp\Livewire\Product\WarehouseList;
use Livewire\Livewire;
use Tests\TestCase;

class WarehouseListTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(WarehouseList::class)
            ->assertStatus(200);
    }
}
