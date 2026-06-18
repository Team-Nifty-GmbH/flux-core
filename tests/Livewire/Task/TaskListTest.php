<?php

use FluxErp\Livewire\Task\TaskList;
use FluxErp\Models\Task;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\Livewire;

test('create new task', function (): void {
    Livewire::actingAs($this->user)
        ->test(TaskList::class)
        ->call('edit')
        ->assertOpensModal('new-task-modal')
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

test('list is selectable', function (): void {
    Livewire::actingAs($this->user)
        ->test(TaskList::class)
        ->assertSet('isSelectable', true);
});

test('delete selected deletes the selected tasks', function (): void {
    $task = Task::factory()->create(['name' => Str::uuid(), 'state' => 'open']);

    Livewire::actingAs($this->user)
        ->test(TaskList::class)
        ->set('selected', [$task->getKey()])
        ->call('deleteSelected')
        ->assertSet('selected', []);

    $this->assertSoftDeleted($task);
});

test('delete selected does nothing without selection', function (): void {
    $task = Task::factory()->create(['name' => Str::uuid(), 'state' => 'open']);

    Livewire::actingAs($this->user)
        ->test(TaskList::class)
        ->set('selected', [])
        ->call('deleteSelected');

    $this->assertModelExists($task);
});

test('open change state modal resets state and opens modal', function (): void {
    Livewire::actingAs($this->user)
        ->test(TaskList::class)
        ->set('selectedState', 'open')
        ->call('openChangeStateModal')
        ->assertSet('selectedState', null)
        ->assertOpensModal('change-task-state-modal');
});

test('change state updates the selected tasks', function (): void {
    $task = Task::factory()->create(['name' => Str::uuid(), 'state' => 'open']);

    $component = Livewire::actingAs($this->user)
        ->test(TaskList::class);

    $state = data_get(Arr::first($component->get('availableStates')), 'name');

    $component
        ->set('selected', [$task->getKey()])
        ->set('selectedState', $state)
        ->call('changeState')
        ->assertReturned(true)
        ->assertSet('selected', [])
        ->assertSet('selectedState', null);

    $this->assertDatabaseHas('tasks', [
        'id' => $task->getKey(),
        'state' => $state,
    ]);
});

test('change state rejects invalid state with a validation error', function (): void {
    $task = Task::factory()->create(['name' => Str::uuid(), 'state' => 'open']);

    Livewire::actingAs($this->user)
        ->test(TaskList::class)
        ->set('selected', [$task->getKey()])
        ->set('selectedState', 'not-a-real-state')
        ->call('changeState')
        ->assertReturned(false)
        ->assertHasErrors('selectedState');

    $this->assertDatabaseHas('tasks', [
        'id' => $task->getKey(),
        'state' => 'open',
    ]);
});
