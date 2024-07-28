<?php

namespace Tests\Feature\Livewire\DataTables;

use FluxErp\Livewire\DataTables\WorkTimeTypeList;
use Livewire\Livewire;
use Tests\TestCase;

class WorkTimeTypeListTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(WorkTimeTypeList::class)
            ->assertStatus(200);
    }
}
