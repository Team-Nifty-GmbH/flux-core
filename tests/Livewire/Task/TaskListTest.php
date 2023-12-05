<?php

namespace FluxErp\Tests\Livewire\Task;

use FluxErp\Livewire\Project\ProjectList;
use FluxErp\Livewire\Task\TaskList;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class TaskListTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_renders_successfully()
    {
        Livewire::test(TaskList::class)
            ->assertStatus(200);
    }
}
