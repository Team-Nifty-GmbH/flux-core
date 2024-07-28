<?php

namespace Tests\Feature\Livewire\DataTables;

use FluxErp\Livewire\DataTables\ScheduleList;
use Livewire\Livewire;
use Tests\TestCase;

class ScheduleListTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(ScheduleList::class)
            ->assertStatus(200);
    }
}
