<?php

use Carbon\Carbon;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\Permission;
use FluxErp\Models\Project;
use FluxErp\Models\Task;
use FluxErp\Models\User;
use FluxErp\Notifications\Task\TaskAssignedNotification;
use FluxErp\States\Task\Done;
use FluxErp\States\Task\Open;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->project = Project::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    $this->tasks = Task::factory()->count(3)->create([
        'project_id' => $this->project->id,
        'responsible_user_id' => $this->user->id,
    ]);

    $this->additionalColumns = AdditionalColumn::query()
        ->where('model_type', morph_alias(Task::class))
        ->get();

    $this->permissions = [
        'show' => Permission::findOrCreate('api.tasks.{id}.get'),
        'index' => Permission::findOrCreate('api.tasks.get'),
        'create' => Permission::findOrCreate('api.tasks.post'),
        'update' => Permission::findOrCreate('api.tasks.put'),
        'delete' => Permission::findOrCreate('api.tasks.{id}.delete'),
        'finish' => Permission::findOrCreate('api.tasks.finish.post'),
        'import' => Permission::findOrCreate('api.tasks.import.post'),
    ];
});

test('bulk update tasks with additional columns', function (): void {
    $additionalColumns[] = AdditionalColumn::factory()->create([
        'model_type' => morph_alias(Task::class),
    ]);
    $additionalColumns[] = AdditionalColumn::factory()->create([
        'model_type' => morph_alias(Task::class),
        'values' => ['a', 'b', 'c'],
    ]);

    $tasks = [
        [
            'id' => $this->tasks[0]->id,
            'project_id' => null,
            'name' => 'test',
            'description' => Str::random(),
            'start_date' => $this->tasks[0]->start_date?->toDateString(),
            'due_date' => $this->tasks[0]->due_date?->toDateString(),
        ],
        [
            'id' => $this->tasks[1]->id,
            'project_id' => null,
            'name' => 'test',
            'description' => Str::random(),
            'start_date' => $this->tasks[1]->start_date?->toDateString(),
            'due_date' => $this->tasks[1]->due_date?->toDateString(),
        ],
    ];

    $this->additionalColumns = AdditionalColumn::query()
        ->where('model_type', morph_alias(Task::class))
        ->get();

    foreach ($tasks as $key => $task) {
        foreach ($this->additionalColumns as $additionalColumn) {
            $tasks[$key] += [
                $additionalColumn->name => is_array($additionalColumn->values) ? $additionalColumn->values[0] : 'Value',
            ];
        }
    }

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/tasks', $tasks);
    $response->assertOk();

    $responseTasks = collect(json_decode($response->getContent())->data->items);
    expect(count($responseTasks))->toEqual(2);

    $dbTasks = Task::query()
        ->whereIn('id', $responseTasks->pluck('id')->toArray())
        ->get();
    expect(count($dbTasks))->toEqual(2);
    expect($dbTasks[0]->id)->toEqual($tasks[0]['id']);
    expect($dbTasks[0]->project_id)->toEqual($tasks[0]['project_id']);
    expect($dbTasks[0]->name)->toEqual($tasks[0]['name']);
    expect($dbTasks[0]->description)->toEqual($tasks[0]['description']);
    expect($this->user->is($dbTasks[0]->getUpdatedBy()))->toBeTrue();

    expect($dbTasks[1]->id)->toEqual($tasks[1]['id']);
    expect($dbTasks[1]->project_id)->toEqual($tasks[1]['project_id']);
    expect($dbTasks[1]->name)->toEqual($tasks[1]['name']);
    expect($dbTasks[1]->description)->toEqual($tasks[1]['description']);
    expect($this->user->is($dbTasks[1]->getUpdatedBy()))->toBeTrue();

    expect($dbTasks[0]->{$additionalColumns[0]->name})->toEqual($tasks[0][$additionalColumns[0]->name]);
    expect($dbTasks[0]->{$additionalColumns[1]->name})->toEqual($tasks[0][$additionalColumns[1]->name]);
    expect($dbTasks[1]->{$additionalColumns[0]->name})->toEqual($tasks[1][$additionalColumns[0]->name]);
    expect($dbTasks[1]->{$additionalColumns[1]->name})->toEqual($tasks[1][$additionalColumns[1]->name]);
});

test('create task', function (): void {
    Notification::fake();
    config(['queue.default' => 'sync']);

    $users = User::factory()->count(3)->create([
        'language_id' => $this->user->language_id,
        'is_active' => true,
    ]);

    $task = [
        'project_id' => $this->project->id,
        'responsible_user_id' => $this->user->id,
        'name' => 'test',
        'description' => Str::random(),
        'start_date' => date('Y-m-d'),
        'due_date' => date('Y-m-t'),
        'priority' => rand(0, 5),
        'time_budget' => '34:56',
        'budget' => rand(0, 10000) / 100,
        'users' => $users->pluck('id')->toArray(),
    ];

    foreach ($this->additionalColumns as $additionalColumn) {
        $task += [
            $additionalColumn->name => is_array($additionalColumn->values) ? $additionalColumn->values[0] : 'Value',
        ];
    }

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/tasks', $task);
    $response->assertCreated();

    $responseTask = json_decode($response->getContent())->data;
    $dbTask = Task::query()
        ->whereKey($responseTask->id)
        ->first();

    expect($dbTask)->not->toBeEmpty();
    expect($dbTask->project_id)->toEqual($task['project_id']);
    expect($dbTask->responsible_user_id)->toEqual($task['responsible_user_id']);
    expect($dbTask->name)->toEqual($task['name']);
    expect($dbTask->description)->toEqual($task['description']);
    expect(Carbon::parse($dbTask->start_date)->toDateString())->toEqual($task['start_date']);
    expect(Carbon::parse($dbTask->due_date)->toDateString())->toEqual($task['due_date']);
    expect($dbTask->priority)->toEqual($task['priority']);
    expect($dbTask->state::$name)->toEqual(Open::$name);
    expect($dbTask->progress)->toEqual(0);
    expect($dbTask->time_budget)->toEqual($task['time_budget']);
    expect($dbTask->budget)->toEqual($task['budget']);
    expect($this->user->is($dbTask->getCreatedBy()))->toBeTrue();
    expect($this->user->is($dbTask->getUpdatedBy()))->toBeTrue();
    expect($dbTask->users()->pluck('users.id')->toArray())->toEqual($task['users']);

    Notification::assertSentTo(
        User::query()
            ->whereKeyNot($this->user->getKey())
            ->whereIntegerInRaw(
                'id',
                array_filter(
                    array_merge(
                        data_get($task, 'users'),
                        [data_get($task, 'responsible_user_id')]
                    )
                )
            )
            ->get(),
        TaskAssignedNotification::class
    );
    Notification::assertNothingSentTo($this->user);

    foreach ($this->additionalColumns as $additionalColumn) {
        expect($responseTask->{$additionalColumn->name})->toEqual($task[$additionalColumn->name]);
        expect($dbTask->{$additionalColumn->name})->toEqual($task[$additionalColumn->name]);
    }
});

test('create task project not found', function (): void {
    $task = [
        'project_id' => ++$this->project->id,
        'name' => 'test',
    ];

    foreach ($this->additionalColumns as $additionalColumn) {
        $task += [
            $additionalColumn->name => is_array($additionalColumn->values) ? $additionalColumn->values[0] : 'Value',
        ];
    }

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/tasks', $task);
    $response->assertUnprocessable();
});

test('create task user not found', function (): void {
    $task = [
        'responsible_user_id' => ++$this->user->id,
        'name' => 'test',
    ];

    foreach ($this->additionalColumns as $additionalColumn) {
        $task += [
            $additionalColumn->name => is_array($additionalColumn->values) ? $additionalColumn->values[0] : 'Value',
        ];
    }

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/tasks', $task);
    $response->assertUnprocessable();
});

test('create task validation fails', function (): void {
    $task = [
        'name' => 'test',
        'time_budget' => rand(0, 10),
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/tasks', $task);
    $response->assertUnprocessable();
});

test('delete task', function (): void {
    AdditionalColumn::factory()->create([
        'model_type' => morph_alias(Task::class),
    ]);

    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/tasks/' . $this->tasks[1]->id);
    $response->assertNoContent();

    $task = $this->tasks[1]->fresh();
    expect($task->deleted_at)->not->toBeNull();
    expect($this->user->is($task->getDeletedBy()))->toBeTrue();
});

test('delete task task not found', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/tasks/' . ++$this->tasks[2]->id);
    $response->assertNotFound();
});

test('finish task', function (): void {
    AdditionalColumn::factory()->create([
        'model_type' => app(Task::class)->getMorphClass(),
    ]);

    $task = [
        'id' => $this->tasks[0]->id,
        'finish' => true,
    ];

    $this->user->givePermissionTo($this->permissions['finish']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/tasks/finish', $task);
    $response->assertOk();

    $responseTask = json_decode($response->getContent())->data;
    $dbTask = Task::query()
        ->whereKey($responseTask->id)
        ->first();

    expect($dbTask)->not->toBeEmpty();
    expect($dbTask->is_done)->toEqual($task['finish']);
});

test('finish task task not found', function (): void {
    $task = [
        'id' => ++$this->tasks[2]->id,
        'finish' => true,
    ];

    $this->user->givePermissionTo($this->permissions['finish']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/tasks/finish', $task);
    $response->assertUnprocessable();
});

test('finish task validation fails', function (): void {
    $task = [
        'id' => $this->tasks[0]->id,
        'finish' => 'true',
    ];

    $this->user->givePermissionTo($this->permissions['finish']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/tasks/finish', $task);
    $response->assertUnprocessable();
});

test('get task', function (): void {
    $this->tasks[0] = $this->tasks[0]->refresh();

    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/tasks/' . $this->tasks[0]->id);
    $response->assertOk();

    $json = json_decode($response->getContent());
    $task = $json->data;
    expect($task)->not->toBeEmpty();
    expect($task->id)->toEqual($this->tasks[0]->id);
    expect($task->project_id)->toEqual($this->tasks[0]->project_id);
    expect($task->responsible_user_id)->toEqual($this->tasks[0]->responsible_user_id);
    expect($task->order_position_id)->toEqual($this->tasks[0]->order_position_id);
    expect($task->name)->toEqual($this->tasks[0]->name);
    expect($task->description)->toEqual($this->tasks[0]->description);
    expect(! is_null($task->start_date) ? Carbon::parse($task->start_date) : null)->toEqual($this->tasks[0]->start_date);
    expect(! is_null($task->due_date) ? Carbon::parse($task->due_date) : null)->toEqual($this->tasks[0]->due_date);
    expect($task->priority)->toEqual($this->tasks[0]->priority);
    expect($task->state)->toEqual($this->tasks[0]->state);
    expect($task->progress)->toEqual($this->tasks[0]->progress);
    expect($task->time_budget)->toEqual($this->tasks[0]->time_budget);
    expect($task->budget)->toEqual($this->tasks[0]->budget);
    expect(Carbon::parse($task->created_at))->toEqual(Carbon::parse($this->tasks[0]->created_at));
    expect(Carbon::parse($task->updated_at))->toEqual(Carbon::parse($this->tasks[0]->updated_at));
});

test('get task task not found', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/tasks/' . ++$this->tasks[2]->id);
    $response->assertNotFound();
});

test('get tasks', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/tasks');
    $response->assertOk();

    $json = json_decode($response->getContent());
    $tasks = $json->data->data;
    $referenceTask = Task::query()->first();
    expect($tasks)->not->toBeEmpty();
    expect($tasks[0]->id)->toEqual($referenceTask->id);
    expect($tasks[0]->project_id)->toEqual($referenceTask->project_id);
    expect($tasks[0]->responsible_user_id)->toEqual($referenceTask->responsible_user_id);
    expect($tasks[0]->order_position_id)->toEqual($referenceTask->order_position_id);
    expect($tasks[0]->name)->toEqual($referenceTask->name);
    expect($tasks[0]->description)->toEqual($referenceTask->description);
    expect(! is_null($tasks[0]->start_date) ? Carbon::parse($tasks[0]->start_date) : null)->toEqual($referenceTask->start_date);
    expect(! is_null($tasks[0]->due_date) ? Carbon::parse($tasks[0]->due_date) : null)->toEqual($referenceTask->due_date);
    expect($tasks[0]->priority)->toEqual($referenceTask->priority);
    expect($tasks[0]->state)->toEqual($referenceTask->state);
    expect($tasks[0]->progress)->toEqual($referenceTask->progress);
    expect($tasks[0]->time_budget)->toEqual($referenceTask->time_budget);
    expect($tasks[0]->budget)->toEqual($referenceTask->budget);
    expect(Carbon::parse($tasks[0]->created_at))->toEqual(Carbon::parse($referenceTask->created_at));
    expect(Carbon::parse($tasks[0]->updated_at))->toEqual(Carbon::parse($referenceTask->updated_at));
});

test('update task', function (): void {
    $task = [
        'id' => $this->tasks[0]->id,
        'project_id' => null,
        'responsible_user_id' => null,
        'name' => Str::random(),
        'description' => Str::random(),
        'start_date' => date('Y-m-d'),
        'due_date' => date('Y-m-t'),
        'priority' => rand(0, 5),
        'state' => Done::$name,
        'time_budget' => '76:54',
        'budget' => rand(0, 10000) / 100,
        'users' => [$this->user->id],
    ];

    foreach ($this->additionalColumns as $additionalColumn) {
        $task += [
            $additionalColumn->name => is_array($additionalColumn->values) ? $additionalColumn->values[0] : 'Value',
        ];
    }

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/tasks', $task);
    $response->assertOk();

    $responseTask = json_decode($response->getContent())->data;
    $dbTask = Task::query()
        ->whereKey($responseTask->id)
        ->first();

    expect($dbTask)->not->toBeEmpty();
    expect($dbTask->id)->toEqual($task['id']);
    expect($dbTask->project_id)->toEqual($task['project_id']);
    expect($dbTask->responsible_user_id)->toEqual($task['responsible_user_id']);
    expect($dbTask->name)->toEqual($task['name']);
    expect($dbTask->description)->toEqual($task['description']);
    expect(Carbon::parse($dbTask->start_date)->toDateString())->toEqual($task['start_date']);
    expect(Carbon::parse($dbTask->due_date)->toDateString())->toEqual($task['due_date']);
    expect($dbTask->priority)->toEqual($task['priority']);
    expect($dbTask->state::$name)->toEqual($task['state']);
    expect($dbTask->progress)->toEqual(1);
    expect($dbTask->time_budget)->toEqual($task['time_budget']);
    expect($dbTask->budget)->toEqual($task['budget']);
    expect($this->user->is($dbTask->getUpdatedBy()))->toBeTrue();
    expect($dbTask->users()->pluck('users.id')->toArray())->toEqual($task['users']);

    foreach ($this->additionalColumns as $additionalColumn) {
        expect($responseTask->{$additionalColumn->name})->toEqual($task[$additionalColumn->name]);
        expect($dbTask->{$additionalColumn->name})->toEqual($task[$additionalColumn->name]);
    }
});

test('update task task not found', function (): void {
    $task = [
        'id' => ++$this->tasks[2]->id,
        'name' => 'test',
        'start_date' => null,
        'due_date' => null,
    ];

    foreach ($this->additionalColumns as $additionalColumn) {
        $task += [
            $additionalColumn->name => is_array($additionalColumn->values) ? $additionalColumn->values[0] : 'Value',
        ];
    }

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/tasks', $task);
    $response->assertUnprocessable();
});

test('update task user not found', function (): void {
    $task = [
        'id' => $this->tasks[0]->id,
        'responsible_user_id' => ++$this->user->id,
        'name' => 'test',
        'start_date' => null,
        'due_date' => null,
    ];

    foreach ($this->additionalColumns as $additionalColumn) {
        $task += [
            $additionalColumn->name => is_array($additionalColumn->values) ? $additionalColumn->values[0] : 'Value',
        ];
    }

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/tasks', $task);
    $response->assertUnprocessable();
});

test('update task validation fails', function (): void {
    $task = [
        'id' => $this->tasks[0]->id,
        'name' => 'test',
        'state' => Str::random(),
        'start_date' => null,
        'due_date' => null,
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/tasks', $task);
    $response->assertUnprocessable();
});

test('update task with project id', function (): void {
    $this->tasks[2] = $this->tasks[2]->refresh();
    $project = Project::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    $task = [
        'id' => $this->tasks[2]->id,
        'project_id' => $project->id,
        'name' => 'test',
        'start_date' => null,
        'due_date' => null,
    ];

    foreach ($this->additionalColumns as $additionalColumn) {
        $task += [
            $additionalColumn->name => is_array($additionalColumn->values) ? $additionalColumn->values[0] : 'Value',
        ];
    }

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/tasks', $task);
    $response->assertOk();

    $responseTask = json_decode($response->getContent())->data;
    $dbTask = Task::query()
        ->whereKey($responseTask->id)
        ->first();

    expect($dbTask)->not->toBeEmpty();
    expect($dbTask->id)->toEqual($task['id']);
    expect($dbTask->project_id)->toEqual($task['project_id']);
    expect($dbTask->responsible_user_id)->toEqual($this->tasks[2]->responsible_user_id);
    expect($dbTask->name)->toEqual($task['name']);
    expect($dbTask->description)->toEqual($this->tasks[2]->description);
    expect($dbTask->start_date)->toEqual($task['start_date']);
    expect($dbTask->due_date)->toEqual($task['due_date']);
    expect($dbTask->priority)->toEqual($this->tasks[2]->priority);
    expect($dbTask->state::$name)->toEqual($this->tasks[2]->state::$name);
    expect($dbTask->progress)->toEqual($this->tasks[2]->progress);
    expect($dbTask->time_budget)->toEqual($this->tasks[2]->time_budget);
    expect($dbTask->budget)->toEqual($this->tasks[2]->budget);
    expect($this->user->is($dbTask->getUpdatedBy()))->toBeTrue();
    expect($dbTask->users()->pluck('users.id')->toArray())->toEqual([]);

    foreach ($this->additionalColumns as $additionalColumn) {
        expect($responseTask->{$additionalColumn->name})->toEqual($task[$additionalColumn->name]);
        expect($dbTask->{$additionalColumn->name})->toEqual($task[$additionalColumn->name]);
    }
});
