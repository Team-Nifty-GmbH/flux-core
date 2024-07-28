<?php

namespace Tests\Feature\Livewire\DataTables;

use FluxErp\Livewire\DataTables\TaskList;
use Livewire\Livewire;
use Tests\TestCase;

class TaskListTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(TaskList::class)
            ->assertStatus(200);
    }
}
