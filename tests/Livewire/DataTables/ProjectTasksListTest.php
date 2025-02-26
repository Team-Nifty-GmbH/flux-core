<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\TaskList;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class ProjectTasksListTest extends BaseSetup
{
    public function test_renders_successfully()
    {
        Livewire::test(TaskList::class)
            ->assertStatus(200);
    }
}
