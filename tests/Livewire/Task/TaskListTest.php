<?php

use FluxErp\Livewire\Task\TaskList;
use FluxErp\Models\Task;
use Illuminate\Support\Str;
use Livewire\Livewire;

test('create new task', function (): void {
    Livewire::actingAs($this->user)
        ->test(TaskList::class)
        ->call('show')
        ->assertSet('task.responsible_user_id', $this->user->id)
        ->set('task.name', $taskName = Str::uuid())
        ->set('task.description', $taskDescription = Str::uuid())
        ->set('task.due_date', $taskDueDate = now()->addDays(4)->format('Y-m-d'))
        ->set('task.start_date', $taskStartDate = now()->addDays(3)->format('Y-m-d'))
        ->set('task.priority', 1)
        ->call('save')
        ->assertHasNoErrors()
        ->assertOk()
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
});

test('renders successfully', function (): void {
    Livewire::test(TaskList::class)
        ->assertOk();
});
