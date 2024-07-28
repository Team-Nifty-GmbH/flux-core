<?php

namespace Tests\Feature\Livewire\DataTables;

use FluxErp\Livewire\DataTables\QueueMonitorList;
use Livewire\Livewire;
use Tests\TestCase;

class QueueMonitorListTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(QueueMonitorList::class)
            ->assertStatus(200);
    }
}
