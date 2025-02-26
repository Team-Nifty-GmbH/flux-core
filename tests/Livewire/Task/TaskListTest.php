<?php

namespace FluxErp\Tests\Livewire\Task;

use FluxErp\Livewire\Task\TaskList;
use FluxErp\Models\Task;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Support\Str;
use Livewire\Livewire;

class TaskListTest extends BaseSetup
{
    public function test_renders_successfully()
    {
        Livewire::test(TaskList::class)
            ->assertStatus(200);
    }

    public function test_create_new_task()
    {
        Livewire::actingAs($this->user)
            ->test(TaskList::class)
            ->call('resetForm')
            ->assertSet('task.responsible_user_id', $this->user->id)
            ->set('task.name', $taskName = Str::uuid())
            ->set('task.description', $taskDescription = Str::uuid())
            ->set('task.due_date', $taskDueDate = now()->addDays(4)->format('Y-m-d'))
            ->set('task.start_date', $taskStartDate = now()->addDays(3)->format('Y-m-d'))
            ->set('task.priority', 1)
            ->call('save')
            ->assertHasNoErrors()
            ->assertStatus(200)
            ->assertReturned(true);

        $this->assertDatabaseHas('tasks', [
            'responsible_user_id' => $this->user->id,
            'name' => $taskName,
            'description' => $taskDescription,
            'due_date' => $taskDueDate,
            'start_date' => $taskStartDate,
            'priority' => 1,
        ]);
        $this->assertDatabaseHas('task_user', [
            'user_id' => $this->user->id,
            'task_id' => Task::query()->where('name', $taskName)->value('id'),
        ]);
    }
}
