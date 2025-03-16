<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\WarehouseList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class WarehouseListTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(WarehouseList::class)
            ->assertStatus(200);
    }
}
