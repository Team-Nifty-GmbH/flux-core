<?php

uses(FluxErp\Tests\Feature\Web\BaseSetup::class);
use FluxErp\Models\Permission;
use FluxErp\Models\Project;

beforeEach(function (): void {
    $this->project = Project::factory()->create([
        'client_id' => $this->dbClient->getKey(),
    ]);
});

test('projects id no user', function (): void {
    $this->get('/projects/' . $this->project->id)
        ->assertStatus(302)
        ->assertRedirect(route('login'));
});

test('projects id page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('projects.{id}.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/projects/' . $this->project->id)
        ->assertStatus(200);
});

test('projects id project not found', function (): void {
    $this->project->delete();

    $this->user->givePermissionTo(Permission::findOrCreate('projects.{id}.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/projects/' . $this->project->id)
        ->assertStatus(404);
});

test('projects id without permission', function (): void {
    Permission::findOrCreate('projects.{id}.get', 'web');

    $this->actingAs($this->user, 'web')->get('/projects/' . $this->project->id)
        ->assertStatus(403);
});

test('projects list no user', function (): void {
    $this->get('/projects')
        ->assertStatus(302)
        ->assertRedirect(route('login'));
});

test('projects list page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('projects.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/projects')
        ->assertStatus(200);
});

test('projects list without permission', function (): void {
    Permission::findOrCreate('projects.get', 'web');

    $this->actingAs($this->user, 'web')->get('/projects')
        ->assertStatus(403);
});
