<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\QueueMonitorList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class QueueMonitorListTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(QueueMonitorList::class)
            ->assertStatus(200);
    }
}
