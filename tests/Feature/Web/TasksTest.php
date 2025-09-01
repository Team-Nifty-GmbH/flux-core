<?php

uses(FluxErp\Tests\Feature\Web\BaseSetup::class);
use FluxErp\Models\Permission;
use FluxErp\Models\Task;

beforeEach(function (): void {
    $this->task = Task::factory()->create();
});

test('tasks id no user', function (): void {
    $this->get('/tasks/' . $this->task->id)
        ->assertStatus(302)
        ->assertRedirect(route('login'));
});

test('tasks id page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('tasks.{id}.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/tasks/' . $this->task->id)
        ->assertStatus(200);
});

test('tasks id task not found', function (): void {
    $this->task->delete();

    $this->user->givePermissionTo(Permission::findOrCreate('tasks.{id}.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/tasks/' . $this->task->id)
        ->assertStatus(404);
});

test('tasks id without permission', function (): void {
    Permission::findOrCreate('tasks.{id}.get', 'web');

    $this->actingAs($this->user, 'web')->get('/tasks/' . $this->task->id)
        ->assertStatus(403);
});

test('tasks list no user', function (): void {
    $this->get('/tasks')
        ->assertStatus(302)
        ->assertRedirect(route('login'));
});

test('tasks list page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('tasks.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/tasks')
        ->assertStatus(200);
});

test('tasks list without permission', function (): void {
    Permission::findOrCreate('tasks.get', 'web');

    $this->actingAs($this->user, 'web')->get('/tasks')
        ->assertStatus(403);
});
