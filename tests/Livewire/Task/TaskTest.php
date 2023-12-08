<?php

namespace FluxErp\Tests\Livewire\Task;

use FluxErp\Livewire\Task\Task as TaskView;
use FluxErp\Models\Task;
use FluxErp\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class TaskTest extends TestCase
{
    use DatabaseTransactions;

    private Task $task;

    public function setUp(): void
    {
        parent::setUp();

        $this->task = Task::factory()->create();
    }

    public function test_renders_successfully()
    {
        Livewire::test(TaskView::class, ['id' => $this->task->id])
            ->assertStatus(200);
    }
}
