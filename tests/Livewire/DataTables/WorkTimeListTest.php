<?php

namespace Tests\Feature\Livewire\DataTables;

use FluxErp\Livewire\DataTables\WorkTimeList;
use Livewire\Livewire;
use Tests\TestCase;

class WorkTimeListTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(WorkTimeList::class)
            ->assertStatus(200);
    }
}
