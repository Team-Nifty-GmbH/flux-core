<?php

use FluxErp\Livewire\Project\ProjectTaskList;
use FluxErp\Models\Project;
use FluxErp\Models\Task;
use Illuminate\Support\Str;
use Livewire\Livewire;

test('can add new task', function (): void {
    $project = Project::factory()->create([
        'tenant_id' => $this->dbTenant->id,
    ]);

    Livewire::test(ProjectTaskList::class, ['projectId' => $project->id])
        ->assertOk()
        ->call('edit')
        ->assertExecutesJs("\$tsui.open.modal('task-form-modal');")
        ->assertSet('task.project_id', $project->id)
        ->assertSet('task.responsible_user_id', $this->user->getKey())
        ->assertSet('task.users', [$this->user->getKey()])
        ->set('task.name', $uuid = Str::uuid()->toString())
        ->call('save')
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertDatabaseHas('tasks', [
        'name' => $uuid,
        'project_id' => $project->id,
        'responsible_user_id' => $this->user->getKey(),
        'state' => 'open',
    ]);
});

test('kanbanMoveItem changes task state', function (): void {
    $project = Project::factory()->create([
        'tenant_id' => $this->dbTenant->id,
    ]);

    $task = Task::factory()->create([
        'project_id' => $project->getKey(),
        'state' => 'open',
    ]);

    Livewire::test(ProjectTaskList::class, ['projectId' => $project->getKey()])
        ->call('kanbanMoveItem', $task->getKey(), 'in_progress');

    expect($task->fresh()->state::$name)->toBe('in_progress');
});

test('kanbanMoveItem skips when target lane equals current state', function (): void {
    $project = Project::factory()->create([
        'tenant_id' => $this->dbTenant->id,
    ]);

    $task = Task::factory()->create([
        'project_id' => $project->getKey(),
        'state' => 'open',
    ]);

    Livewire::test(ProjectTaskList::class, ['projectId' => $project->getKey()])
        ->call('kanbanMoveItem', $task->getKey(), 'open');

    expect($task->fresh()->state::$name)->toBe('open');
});

test('sortRows updates priorities within a lane', function (): void {
    $project = Project::factory()->create([
        'tenant_id' => $this->dbTenant->id,
    ]);

    $tasks = collect();
    for ($i = 0; $i < 3; $i++) {
        $tasks->push(Task::factory()->create([
            'project_id' => $project->getKey(),
            'state' => 'open',
            'priority' => 0,
        ]));
    }

    // Move last task to position 0 (top)
    $movedTask = $tasks->last();

    Livewire::test(ProjectTaskList::class, ['projectId' => $project->getKey()])
        ->call('sortRows', $movedTask->getKey(), 0);

    // Moved task should have the highest priority (top of DESC order)
    $movedTask->refresh();
    $otherPriorities = Task::query()
        ->where('project_id', $project->getKey())
        ->where('state', 'open')
        ->whereKeyNot($movedTask->getKey())
        ->pluck('priority')
        ->toArray();

    expect($movedTask->priority)->toBeGreaterThan(max($otherPriorities));
});

test('sortRows only affects tasks in the same lane', function (): void {
    $project = Project::factory()->create([
        'tenant_id' => $this->dbTenant->id,
    ]);

    $openTask = Task::factory()->create([
        'project_id' => $project->getKey(),
        'state' => 'open',
        'priority' => 5,
    ]);

    $inProgressTask = Task::factory()->create([
        'project_id' => $project->getKey(),
        'state' => 'in_progress',
        'priority' => 10,
    ]);

    Livewire::test(ProjectTaskList::class, ['projectId' => $project->getKey()])
        ->call('sortRows', $openTask->getKey(), 0);

    // in_progress task priority should be unchanged
    expect($inProgressTask->fresh()->priority)->toBe(10);
});

test('isSortable returns true only for kanban layout', function (): void {
    $project = Project::factory()->create([
        'tenant_id' => $this->dbTenant->id,
    ]);

    $component = Livewire::test(ProjectTaskList::class, ['projectId' => $project->getKey()]);

    // Table layout — not sortable
    expect($component->instance()->activeLayout)->toBe('table');

    $component->call('setLayout', 'kanban');
    expect($component->instance()->activeLayout)->toBe('kanban');
});

test('availableLayouts includes table and kanban', function (): void {
    $project = Project::factory()->create([
        'tenant_id' => $this->dbTenant->id,
    ]);

    $component = Livewire::test(ProjectTaskList::class, ['projectId' => $project->getKey()]);
    $viewData = $component->instance()->getIslandData();

    expect($viewData['availableLayouts'])->toBe(['table', 'kanban']);
});
