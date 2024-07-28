<?php

namespace Tests\Feature\Livewire\DataTables;

use FluxErp\Livewire\DataTables\SerialNumberRangeList;
use Livewire\Livewire;
use Tests\TestCase;

class SerialNumberRangeListTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(SerialNumberRangeList::class)
            ->assertStatus(200);
    }
}
