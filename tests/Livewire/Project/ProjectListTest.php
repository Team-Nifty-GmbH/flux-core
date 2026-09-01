<?php

use FluxErp\Livewire\Project\ProjectList;
use FluxErp\Models\Project;
use Illuminate\Support\Str;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ProjectList::class)
        ->assertOk();
});

test('createProject resets form and opens modal', function (): void {
    Livewire::test(ProjectList::class)
        ->call('createProject')
        ->assertSet('project.id', null)
        ->assertSet('project.name', null)
        ->assertExecutesJs("\$tsui.open.modal('edit-project');");
});

test('can create a project', function (): void {
    $name = Str::uuid()->toString();

    Livewire::test(ProjectList::class)
        ->call('createProject')
        ->set('project.name', $name)
        ->call('save')
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertDatabaseHas('projects', [
        'name' => $name,
        'tenant_id' => $this->dbTenant->getKey(),
    ]);
});

test('save validation fails without name', function (): void {
    Livewire::test(ProjectList::class)
        ->call('createProject')
        ->call('save')
        ->assertReturned(false);
});

test('project form fills after save', function (): void {
    $name = Str::uuid()->toString();

    $component = Livewire::test(ProjectList::class)
        ->call('createProject')
        ->set('project.name', $name)
        ->call('save')
        ->assertHasNoErrors()
        ->assertReturned(true);

    $project = Project::query()->where('name', $name)->first();

    expect($project)->not->toBeNull()
        ->and($project->tenant_id)->toBe($this->dbTenant->getKey());
});
