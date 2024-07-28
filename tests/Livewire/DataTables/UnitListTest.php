<?php

namespace Tests\Feature\Livewire\DataTables;

use FluxErp\Livewire\DataTables\UnitList;
use Livewire\Livewire;
use Tests\TestCase;

class UnitListTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(UnitList::class)
            ->assertStatus(200);
    }
}
