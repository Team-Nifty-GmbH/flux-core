<?php

use FluxErp\Models\Permission;
use FluxErp\Models\Project;

beforeEach(function (): void {
    $this->project = Project::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);
});

test('projects id no user', function (): void {
    $this->actingAsGuest();

    $this->get('/projects/' . $this->project->id)
        ->assertFound()
        ->assertRedirect(route('login'));
});

test('projects id page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('projects.{id}.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/projects/' . $this->project->id)
        ->assertOk();
});

test('projects id project not found', function (): void {
    $this->project->delete();

    $this->user->givePermissionTo(Permission::findOrCreate('projects.{id}.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/projects/' . $this->project->id)
        ->assertNotFound();
});

test('projects id without permission', function (): void {
    Permission::findOrCreate('projects.{id}.get', 'web');

    $this->actingAs($this->user, 'web')->get('/projects/' . $this->project->id)
        ->assertForbidden();
});

test('projects list no user', function (): void {
    $this->actingAsGuest();

    $this->get('/projects')
        ->assertFound()
        ->assertRedirect(route('login'));
});

test('projects list page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('projects.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/projects')
        ->assertOk();
});

test('projects list without permission', function (): void {
    Permission::findOrCreate('projects.get', 'web');

    $this->actingAs($this->user, 'web')->get('/projects')
        ->assertForbidden();
});
