<?php

use FluxErp\Models\Permission;
use FluxErp\Models\Task;

beforeEach(function (): void {
    $this->task = Task::factory()->create();
});

test('tasks id no user', function (): void {
    $this->actingAsGuest();

    $this->get('/tasks/' . $this->task->id)
        ->assertFound()
        ->assertRedirect(route('login'));
});

test('tasks id page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('tasks.{id}.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/tasks/' . $this->task->id)
        ->assertOk();
});

test('tasks id task not found', function (): void {
    $this->task->delete();

    $this->user->givePermissionTo(Permission::findOrCreate('tasks.{id}.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/tasks/' . $this->task->id)
        ->assertNotFound();
});

test('tasks id without permission', function (): void {
    Permission::findOrCreate('tasks.{id}.get', 'web');

    $this->actingAs($this->user, 'web')->get('/tasks/' . $this->task->id)
        ->assertForbidden();
});

test('tasks list no user', function (): void {
    $this->actingAsGuest();

    $this->get('/tasks')
        ->assertFound()
        ->assertRedirect(route('login'));
});

test('tasks list page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('tasks.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/tasks')
        ->assertOk();
});

test('tasks list without permission', function (): void {
    Permission::findOrCreate('tasks.get', 'web');

    $this->actingAs($this->user, 'web')->get('/tasks')
        ->assertForbidden();
});
