<?php

use FluxErp\Actions\Task\DeleteTask;
use FluxErp\Actions\Task\UpdateTask;
use FluxErp\Jobs\ExecuteActionsJob;
use FluxErp\Livewire\Task\TaskList;
use FluxErp\Models\Task;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Bus;
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

test('delete selected dispatches execute actions job with delete task action', function (): void {
    Bus::fake();

    $task = Task::factory()->create(['name' => Str::uuid(), 'state' => 'open']);

    Livewire::actingAs($this->user)
        ->test(TaskList::class)
        ->set('selected', [$task->getKey()])
        ->call('deleteSelected')
        ->assertSet('selected', []);

    Bus::assertDispatched(
        ExecuteActionsJob::class,
        fn (ExecuteActionsJob $job): bool => (new ReflectionProperty($job, 'action'))->getValue($job) === DeleteTask::class
            && (new ReflectionProperty($job, 'payloads'))->getValue($job) === [$task->getKey()]
    );
});

test('delete selected does nothing without selection', function (): void {
    Bus::fake();

    Livewire::actingAs($this->user)
        ->test(TaskList::class)
        ->set('selected', [])
        ->call('deleteSelected');

    Bus::assertNotDispatched(ExecuteActionsJob::class);
});

test('open change state modal resets state and opens modal', function (): void {
    Livewire::actingAs($this->user)
        ->test(TaskList::class)
        ->set('selectedState', 'open')
        ->call('openChangeStateModal')
        ->assertSet('selectedState', null)
        ->assertOpensModal('change-task-state-modal');
});

test('change state dispatches execute actions job with update task action', function (): void {
    Bus::fake();

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

    Bus::assertDispatched(
        ExecuteActionsJob::class,
        function (ExecuteActionsJob $job) use ($task, $state): bool {
            $payloads = (new ReflectionProperty($job, 'payloads'))->getValue($job);

            return (new ReflectionProperty($job, 'action'))->getValue($job) === UpdateTask::class
                && data_get($payloads, '0.id') === $task->getKey()
                && data_get($payloads, '0.state') === $state;
        }
    );
});

test('change state rejects invalid state', function (): void {
    Bus::fake();

    $task = Task::factory()->create(['name' => Str::uuid(), 'state' => 'open']);

    Livewire::actingAs($this->user)
        ->test(TaskList::class)
        ->set('selected', [$task->getKey()])
        ->set('selectedState', 'not-a-real-state')
        ->call('changeState')
        ->assertReturned(false);

    Bus::assertNotDispatched(ExecuteActionsJob::class);
});
