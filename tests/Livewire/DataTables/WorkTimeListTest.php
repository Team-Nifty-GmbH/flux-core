<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\WorkTimeList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class WorkTimeListTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(WorkTimeList::class)
            ->assertStatus(200);
    }
}
