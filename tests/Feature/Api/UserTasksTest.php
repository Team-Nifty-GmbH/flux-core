<?php

use FluxErp\Models\Permission;
use FluxErp\Models\Task;
use FluxErp\States\Task\Done as TaskDone;
use FluxErp\States\Task\Open as TaskOpen;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->tasksPermission = Permission::findOrCreate('api.user.tasks.get', 'sanctum');
});

test('the user tasks endpoint returns open tasks the user is responsible for or assigned to', function (): void {
    $mineResponsible = Task::factory()->create(['responsible_user_id' => $this->user->id, 'state' => TaskOpen::class]);
    $mineAssigned = Task::factory()->create(['state' => TaskOpen::class]);
    $mineAssigned->users()->attach($this->user->id);
    $doneMine = Task::factory()->create(['responsible_user_id' => $this->user->id, 'state' => TaskDone::class]);
    $someoneElse = Task::factory()->create(['state' => TaskOpen::class]);

    $this->user->givePermissionTo($this->tasksPermission);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->getJson('/api/user/tasks')->assertOk();

    $ids = collect($response->json('data'))->pluck('id');
    expect($ids)->toContain($mineResponsible->id, $mineAssigned->id);
    expect($ids)->not->toContain($doneMine->id);
    expect($ids)->not->toContain($someoneElse->id);
    expect($response->json('data.0'))->toHaveKeys(['id', 'name', 'state', 'priority', 'due_date', 'url']);
});
