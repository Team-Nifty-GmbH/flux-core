<?php

use FluxErp\Models\Language;
use FluxErp\Models\Permission;
use FluxErp\Models\Task;
use FluxErp\Models\User;
use FluxErp\States\Task\Done as TaskDone;
use FluxErp\States\Task\InProgress as TaskInProgress;
use FluxErp\States\Task\Open as TaskOpen;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->permission = Permission::findOrCreate('api.widgets.my-tasks.get', 'sanctum');
});

test('the my tasks widget api returns only assigned open tasks', function (): void {
    $assignedTask = Task::factory()->create(['state' => TaskOpen::class, 'priority' => 1]);
    $assignedTask->users()->attach($this->user->getKey());
    $highPriorityAssignedTask = Task::factory()->create([
        'state' => TaskInProgress::class,
        'priority' => 5,
    ]);
    $highPriorityAssignedTask->users()->attach($this->user->getKey());
    $responsibleOnlyTask = Task::factory()->create([
        'state' => TaskInProgress::class,
        'responsible_user_id' => $this->user->getKey(),
    ]);
    $doneTask = Task::factory()->create(['state' => TaskDone::class]);
    $doneTask->users()->attach($this->user->getKey());
    $someoneElsesTask = Task::factory()->create(['state' => TaskOpen::class]);

    $this->user->givePermissionTo($this->permission);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->getJson('/api/widgets/my-tasks')->assertOk();

    $data = collect($response->json('data'));
    expect($data->pluck('id'))->toContain($assignedTask->getKey(), $highPriorityAssignedTask->getKey())
        ->and($data->pluck('id'))->not->toContain(
            $responsibleOnlyTask->getKey(),
            $doneTask->getKey(),
            $someoneElsesTask->getKey()
        )
        ->and($data->first()['id'])->toEqual($highPriorityAssignedTask->getKey())
        ->and($data->first())->toHaveKeys(['id', 'name', 'state', 'priority', 'due_date', 'url']);
});

test('the my tasks widget api respects the limit parameter', function (): void {
    Task::factory()
        ->count(3)
        ->create(['state' => TaskOpen::class])
        ->each(fn (Task $task) => $task->users()->attach($this->user->getKey()));

    $this->user->givePermissionTo($this->permission);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->getJson('/api/widgets/my-tasks?limit=2')->assertOk();

    expect($response->json('data'))->toHaveCount(2);

    $this->getJson('/api/widgets/my-tasks?limit=0')
        ->assertUnprocessable()
        ->assertJsonValidationErrors('limit');
});

test('the my tasks widget api forbids users without the permission', function (): void {
    $otherUser = User::factory()->create([
        'language_id' => Language::factory()->create()->id,
    ]);
    Sanctum::actingAs($otherUser, ['user']);

    $this->getJson('/api/widgets/my-tasks')->assertForbidden();
});
