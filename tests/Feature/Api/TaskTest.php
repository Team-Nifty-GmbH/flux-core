<?php

namespace FluxErp\Tests\Feature\Api;

use Carbon\Carbon;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\Permission;
use FluxErp\Models\Project;
use FluxErp\Models\Task;
use FluxErp\Models\User;
use FluxErp\States\Task\Done;
use FluxErp\States\Task\Open;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

class TaskTest extends BaseSetup
{
    private Collection $additionalColumns;

    private array $permissions;

    private Model $project;

    private Collection $tasks;

    protected function setUp(): void
    {
        parent::setUp();
        $this->project = Project::factory()->create([
            'client_id' => $this->dbClient->getKey(),
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
    }

    public function test_bulk_update_tasks_with_additional_columns(): void
    {
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
        $response->assertStatus(200);

        $responseTasks = collect(json_decode($response->getContent())->responses);
        $this->assertEquals(2, count($responseTasks));

        $dbTasks = Task::query()
            ->whereIn('id', $responseTasks->pluck('id')->toArray())
            ->get();
        $this->assertEquals(2, count($dbTasks));
        $this->assertEquals($tasks[0]['id'], $dbTasks[0]->id);
        $this->assertEquals($tasks[0]['project_id'], $dbTasks[0]->project_id);
        $this->assertEquals($tasks[0]['name'], $dbTasks[0]->name);
        $this->assertEquals($tasks[0]['description'], $dbTasks[0]->description);
        $this->assertTrue($this->user->is($dbTasks[0]->getUpdatedBy()));

        $this->assertEquals($tasks[1]['id'], $dbTasks[1]->id);
        $this->assertEquals($tasks[1]['project_id'], $dbTasks[1]->project_id);
        $this->assertEquals($tasks[1]['name'], $dbTasks[1]->name);
        $this->assertEquals($tasks[1]['description'], $dbTasks[1]->description);
        $this->assertTrue($this->user->is($dbTasks[1]->getUpdatedBy()));

        $this->assertEquals($tasks[0][$additionalColumns[0]->name], $dbTasks[0]->{$additionalColumns[0]->name});
        $this->assertEquals($tasks[0][$additionalColumns[1]->name], $dbTasks[0]->{$additionalColumns[1]->name});
        $this->assertEquals($tasks[1][$additionalColumns[0]->name], $dbTasks[1]->{$additionalColumns[0]->name});
        $this->assertEquals($tasks[1][$additionalColumns[1]->name], $dbTasks[1]->{$additionalColumns[1]->name});
    }

    public function test_create_task(): void
    {
        $users = User::factory()->count(3)->create([
            'language_id' => $this->user->language_id,
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
        $response->assertStatus(201);

        $responseTask = json_decode($response->getContent())->data;
        $dbTask = Task::query()
            ->whereKey($responseTask->id)
            ->first();

        $this->assertNotEmpty($dbTask);
        $this->assertEquals($task['project_id'], $dbTask->project_id);
        $this->assertEquals($task['responsible_user_id'], $dbTask->responsible_user_id);
        $this->assertEquals($task['name'], $dbTask->name);
        $this->assertEquals($task['description'], $dbTask->description);
        $this->assertEquals($task['start_date'], Carbon::parse($dbTask->start_date)->toDateString());
        $this->assertEquals($task['due_date'], Carbon::parse($dbTask->due_date)->toDateString());
        $this->assertEquals($task['priority'], $dbTask->priority);
        $this->assertEquals(Open::$name, $dbTask->state::$name);
        $this->assertEquals(0, $dbTask->progress);
        $this->assertEquals($task['time_budget'], $dbTask->time_budget);
        $this->assertEquals($task['budget'], $dbTask->budget);
        $this->assertTrue($this->user->is($dbTask->getCreatedBy()));
        $this->assertTrue($this->user->is($dbTask->getUpdatedBy()));
        $this->assertEquals($task['users'], $dbTask->users()->pluck('users.id')->toArray());

        foreach ($this->additionalColumns as $additionalColumn) {
            $this->assertEquals($task[$additionalColumn->name], $responseTask->{$additionalColumn->name});
            $this->assertEquals($task[$additionalColumn->name], $dbTask->{$additionalColumn->name});
        }
    }

    public function test_create_task_project_not_found(): void
    {
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
        $response->assertStatus(422);
    }

    public function test_create_task_user_not_found(): void
    {
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
        $response->assertStatus(422);
    }

    public function test_create_task_validation_fails(): void
    {
        $task = [
            'name' => 'test',
            'time_budget' => rand(0, 10),
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/tasks', $task);
        $response->assertStatus(422);
    }

    public function test_delete_task(): void
    {
        AdditionalColumn::factory()->create([
            'model_type' => morph_alias(Task::class),
        ]);

        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/tasks/' . $this->tasks[1]->id);
        $response->assertStatus(204);

        $task = $this->tasks[1]->fresh();
        $this->assertNotNull($task->deleted_at);
        $this->assertTrue($this->user->is($task->getDeletedBy()));
    }

    public function test_delete_task_task_not_found(): void
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/tasks/' . ++$this->tasks[2]->id);
        $response->assertStatus(404);
    }

    public function test_finish_task(): void
    {
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
        $response->assertStatus(200);

        $responseTask = json_decode($response->getContent())->data;
        $dbTask = Task::query()
            ->whereKey($responseTask->id)
            ->first();

        $this->assertNotEmpty($dbTask);
        $this->assertEquals($task['finish'], $dbTask->is_done);
    }

    public function test_finish_task_task_not_found(): void
    {
        $task = [
            'id' => ++$this->tasks[2]->id,
            'finish' => true,
        ];

        $this->user->givePermissionTo($this->permissions['finish']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/tasks/finish', $task);
        $response->assertStatus(422);
    }

    public function test_finish_task_validation_fails(): void
    {
        $task = [
            'id' => $this->tasks[0]->id,
            'finish' => 'true',
        ];

        $this->user->givePermissionTo($this->permissions['finish']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/tasks/finish', $task);
        $response->assertStatus(422);
    }

    public function test_get_task(): void
    {
        $this->tasks[0] = $this->tasks[0]->refresh();

        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/tasks/' . $this->tasks[0]->id);
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $task = $json->data;
        $this->assertNotEmpty($task);
        $this->assertEquals($this->tasks[0]->id, $task->id);
        $this->assertEquals($this->tasks[0]->project_id, $task->project_id);
        $this->assertEquals($this->tasks[0]->responsible_user_id, $task->responsible_user_id);
        $this->assertEquals($this->tasks[0]->order_position_id, $task->order_position_id);
        $this->assertEquals($this->tasks[0]->name, $task->name);
        $this->assertEquals($this->tasks[0]->description, $task->description);
        $this->assertEquals($this->tasks[0]->start_date,
            ! is_null($task->start_date) ? Carbon::parse($task->start_date) : null);
        $this->assertEquals($this->tasks[0]->due_date,
            ! is_null($task->due_date) ? Carbon::parse($task->due_date) : null);
        $this->assertEquals($this->tasks[0]->priority, $task->priority);
        $this->assertEquals($this->tasks[0]->state, $task->state);
        $this->assertEquals($this->tasks[0]->progress, $task->progress);
        $this->assertEquals($this->tasks[0]->time_budget, $task->time_budget);
        $this->assertEquals($this->tasks[0]->budget, $task->budget);
        $this->assertEquals(Carbon::parse($this->tasks[0]->created_at),
            Carbon::parse($task->created_at));
        $this->assertEquals(Carbon::parse($this->tasks[0]->updated_at),
            Carbon::parse($task->updated_at));
    }

    public function test_get_task_task_not_found(): void
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/tasks/' . ++$this->tasks[2]->id);
        $response->assertStatus(404);
    }

    public function test_get_tasks(): void
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/tasks');
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $tasks = $json->data->data;
        $referenceTask = Task::query()->first();
        $this->assertNotEmpty($tasks);
        $this->assertEquals($referenceTask->id, $tasks[0]->id);
        $this->assertEquals($referenceTask->project_id, $tasks[0]->project_id);
        $this->assertEquals($referenceTask->responsible_user_id, $tasks[0]->responsible_user_id);
        $this->assertEquals($referenceTask->order_position_id, $tasks[0]->order_position_id);
        $this->assertEquals($referenceTask->name, $tasks[0]->name);
        $this->assertEquals($referenceTask->description, $tasks[0]->description);
        $this->assertEquals($referenceTask->start_date,
            ! is_null($tasks[0]->start_date) ? Carbon::parse($tasks[0]->start_date) : null);
        $this->assertEquals($referenceTask->due_date,
            ! is_null($tasks[0]->due_date) ? Carbon::parse($tasks[0]->due_date) : null);
        $this->assertEquals($referenceTask->priority, $tasks[0]->priority);
        $this->assertEquals($referenceTask->state, $tasks[0]->state);
        $this->assertEquals($referenceTask->progress, $tasks[0]->progress);
        $this->assertEquals($referenceTask->time_budget, $tasks[0]->time_budget);
        $this->assertEquals($referenceTask->budget, $tasks[0]->budget);
        $this->assertEquals(Carbon::parse($referenceTask->created_at),
            Carbon::parse($tasks[0]->created_at));
        $this->assertEquals(Carbon::parse($referenceTask->updated_at),
            Carbon::parse($tasks[0]->updated_at));
    }

    public function test_update_task(): void
    {
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
        $response->assertStatus(200);

        $responseTask = json_decode($response->getContent())->data;
        $dbTask = Task::query()
            ->whereKey($responseTask->id)
            ->first();

        $this->assertNotEmpty($dbTask);
        $this->assertEquals($task['id'], $dbTask->id);
        $this->assertEquals($task['project_id'], $dbTask->project_id);
        $this->assertEquals($task['responsible_user_id'], $dbTask->responsible_user_id);
        $this->assertEquals($task['name'], $dbTask->name);
        $this->assertEquals($task['description'], $dbTask->description);
        $this->assertEquals($task['start_date'], Carbon::parse($dbTask->start_date)->toDateString());
        $this->assertEquals($task['due_date'], Carbon::parse($dbTask->due_date)->toDateString());
        $this->assertEquals($task['priority'], $dbTask->priority);
        $this->assertEquals($task['state'], $dbTask->state::$name);
        $this->assertEquals(1, $dbTask->progress);
        $this->assertEquals($task['time_budget'], $dbTask->time_budget);
        $this->assertEquals($task['budget'], $dbTask->budget);
        $this->assertTrue($this->user->is($dbTask->getUpdatedBy()));
        $this->assertEquals($task['users'], $dbTask->users()->pluck('users.id')->toArray());

        foreach ($this->additionalColumns as $additionalColumn) {
            $this->assertEquals($task[$additionalColumn->name], $responseTask->{$additionalColumn->name});
            $this->assertEquals($task[$additionalColumn->name], $dbTask->{$additionalColumn->name});
        }
    }

    public function test_update_task_task_not_found(): void
    {
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
        $response->assertStatus(422);
    }

    public function test_update_task_user_not_found(): void
    {
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
        $response->assertStatus(422);
    }

    public function test_update_task_validation_fails(): void
    {
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
        $response->assertStatus(422);
    }

    public function test_update_task_with_project_id(): void
    {
        $this->tasks[2] = $this->tasks[2]->refresh();
        $project = Project::factory()->create([
            'client_id' => $this->dbClient->getKey(),
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
        $response->assertStatus(200);

        $responseTask = json_decode($response->getContent())->data;
        $dbTask = Task::query()
            ->whereKey($responseTask->id)
            ->first();

        $this->assertNotEmpty($dbTask);
        $this->assertEquals($task['id'], $dbTask->id);
        $this->assertEquals($task['project_id'], $dbTask->project_id);
        $this->assertEquals($this->tasks[2]->responsible_user_id, $dbTask->responsible_user_id);
        $this->assertEquals($task['name'], $dbTask->name);
        $this->assertEquals($this->tasks[2]->description, $dbTask->description);
        $this->assertEquals($task['start_date'], $dbTask->start_date);
        $this->assertEquals($task['due_date'], $dbTask->due_date);
        $this->assertEquals($this->tasks[2]->priority, $dbTask->priority);
        $this->assertEquals($this->tasks[2]->state::$name, $dbTask->state::$name);
        $this->assertEquals($this->tasks[2]->progress, $dbTask->progress);
        $this->assertEquals($this->tasks[2]->time_budget, $dbTask->time_budget);
        $this->assertEquals($this->tasks[2]->budget, $dbTask->budget);
        $this->assertTrue($this->user->is($dbTask->getUpdatedBy()));
        $this->assertEquals([], $dbTask->users()->pluck('users.id')->toArray());

        foreach ($this->additionalColumns as $additionalColumn) {
            $this->assertEquals($task[$additionalColumn->name], $responseTask->{$additionalColumn->name});
            $this->assertEquals($task[$additionalColumn->name], $dbTask->{$additionalColumn->name});
        }
    }
}
