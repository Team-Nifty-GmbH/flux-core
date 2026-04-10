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

test('edit with null resets form and opens modal', function (): void {
    $project = Project::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    Livewire::test(ProjectTaskList::class, ['projectId' => $project->getKey()])
        ->call('edit')
        ->assertExecutesJs("\$tsui.open.modal('task-form-modal');")
        ->assertSet('task.id', null)
        ->assertSet('task.name', null)
        ->assertSet('task.project_id', $project->getKey())
        ->assertSet('task.responsible_user_id', $this->user->getKey())
        ->assertSet('task.users', [$this->user->getKey()]);
});

test('edit with existing task fills form', function (): void {
    $project = Project::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    $task = Task::factory()->create([
        'project_id' => $project->getKey(),
        'responsible_user_id' => $this->user->getKey(),
    ]);

    Livewire::test(ProjectTaskList::class, ['projectId' => $project->getKey()])
        ->call('edit', $task->getKey())
        ->assertExecutesJs("\$tsui.open.modal('task-form-modal');")
        ->assertSet('task.id', $task->getKey())
        ->assertSet('task.name', $task->name)
        ->assertSet('task.project_id', $project->getKey());
});

test('save validation fails without name', function (): void {
    $project = Project::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    Livewire::test(ProjectTaskList::class, ['projectId' => $project->getKey()])
        ->call('edit')
        ->call('save')
        ->assertReturned(false);
});

test('can update existing task', function (): void {
    $project = Project::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    $task = Task::factory()->create([
        'project_id' => $project->getKey(),
        'responsible_user_id' => $this->user->getKey(),
    ]);

    $newName = Str::uuid()->toString();

    Livewire::test(ProjectTaskList::class, ['projectId' => $project->getKey()])
        ->call('edit', $task->getKey())
        ->set('task.name', $newName)
        ->call('save')
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertDatabaseHas('tasks', [
        'id' => $task->getKey(),
        'name' => $newName,
        'project_id' => $project->getKey(),
    ]);
});

test('can delete task', function (): void {
    $project = Project::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    $task = Task::factory()->create([
        'project_id' => $project->getKey(),
        'responsible_user_id' => $this->user->getKey(),
    ]);

    Livewire::test(ProjectTaskList::class, ['projectId' => $project->getKey()])
        ->call('delete', $task->getKey())
        ->assertReturned(true);

    $this->assertSoftDeleted('tasks', [
        'id' => $task->getKey(),
    ]);
});
