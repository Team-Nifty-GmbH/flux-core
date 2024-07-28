<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\UnitList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class UnitListTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(UnitList::class)
            ->assertStatus(200);
    }
}
