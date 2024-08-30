<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\ScheduleList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class ScheduleListTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(ScheduleList::class)
            ->assertStatus(200);
    }
}
