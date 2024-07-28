<?php

namespace Tests\Feature\Livewire\DataTables;

use FluxErp\Livewire\DataTables\ActivityLogList;
use Livewire\Livewire;
use Tests\TestCase;

class ActivityLogListTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(ActivityLogList::class)
            ->assertStatus(200);
    }
}
