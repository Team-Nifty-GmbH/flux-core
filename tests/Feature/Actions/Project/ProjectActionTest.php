<?php

use FluxErp\Actions\Project\CreateProject;
use FluxErp\Actions\Project\DeleteProject;
use FluxErp\Actions\Project\UpdateProject;
use FluxErp\Models\Project;

test('create project', function (): void {
    $project = CreateProject::make([
        'name' => 'Website Redesign',
        'tenant_id' => $this->dbTenant->getKey(),
    ])->validate()->execute();

    expect($project)->toBeInstanceOf(Project::class)
        ->name->toBe('Website Redesign');
});

test('create project requires name', function (): void {
    CreateProject::assertValidationErrors(
        ['tenant_id' => $this->dbTenant->getKey()],
        'name'
    );
});

test('update project', function (): void {
    $project = Project::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    $updated = UpdateProject::make([
        'id' => $project->getKey(),
        'name' => 'App Rewrite',
    ])->validate()->execute();

    expect($updated->name)->toBe('App Rewrite');
});

test('delete project', function (): void {
    $project = Project::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    expect(DeleteProject::make(['id' => $project->getKey()])
        ->validate()->execute())->toBeTrue();
});
