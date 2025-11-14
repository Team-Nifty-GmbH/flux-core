<?php

use FluxErp\Livewire\Project\ProjectTaskList;
use FluxErp\Models\Project;
use Illuminate\Support\Str;
use Livewire\Livewire;

test('can add new task', function (): void {
    $project = Project::factory()->create([
        'tenant_id' => $this->dbTenant->id,
    ]);

    Livewire::test(ProjectTaskList::class, ['projectId' => $project->id])
        ->assertOk()
        ->call('edit')
        ->assertExecutesJs("\$modalOpen('task-form-modal');")
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
