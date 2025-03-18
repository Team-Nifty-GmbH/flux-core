<?php

namespace FluxErp\Tests\Livewire\Task;

use FluxErp\Livewire\Task\Task as TaskView;
use FluxErp\Models\Task;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class TaskTest extends BaseSetup
{
    private Task $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->task = Task::factory()->create();
    }

    public function test_renders_successfully(): void
    {
        Livewire::test(TaskView::class, ['id' => $this->task->id])
            ->assertStatus(200);
    }

    public function test_switch_tabs(): void
    {
        Livewire::actingAs($this->user)
            ->test(TaskView::class, ['id' => $this->task->id])
            ->cycleTabs('taskTab');
    }
}
