<?php

namespace FluxErp\Tests\Feature;

use Carbon\Carbon;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\Address;
use FluxErp\Models\Category;
use FluxErp\Models\Contact;
use FluxErp\Models\Language;
use FluxErp\Models\Permission;
use FluxErp\Models\Project;
use FluxErp\Models\ProjectTask;
use FluxErp\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;

class ProjectTaskTest extends BaseSetup
{
    use DatabaseTransactions;

    private Model $address;

    private Model $project;

    private Collection $projectCategories;

    private Collection $projectTask;

    private Collection $additionalColumns;

    private array $permissions;

    protected function setUp(): void
    {
        parent::setUp();
        $category = Category::factory()->create(['model_type' => Project::class]);
        $this->project = Project::factory()->create(['category_id' => $category->id]);
        $this->projectCategories = Category::factory()
            ->count(3)
            ->create([
                'model_type' => ProjectTask::class,
                'parent_id' => $category->id,
            ]);

        $contact = Contact::factory()->create(['client_id' => $this->dbClient->id]);
        $this->address = Address::factory()->create(['contact_id' => $contact->id, 'client_id' => $contact->client_id]);
        $this->projectTask = ProjectTask::factory()->count(3)->create([
            'project_id' => $this->project->id,
            'address_id' => $this->address->id,
            'user_id' => $this->user->id,
        ]);

        $this->project->categories()->attach($this->projectCategories->pluck('id')->toArray());

        $this->additionalColumns = AdditionalColumn::query()
            ->where('model_type', ProjectTask::class)
            ->get();

        $this->permissions = [
            'show' => Permission::findOrCreate('api.projects.tasks.{id}.get'),
            'index' => Permission::findOrCreate('api.projects.tasks.get'),
            'create' => Permission::findOrCreate('api.projects.tasks.post'),
            'update' => Permission::findOrCreate('api.projects.tasks.put'),
            'delete' => Permission::findOrCreate('api.projects.tasks.{id}.delete'),
            'finish' => Permission::findOrCreate('api.projects.tasks.finish.post'),
            'import' => Permission::findOrCreate('api.projects.tasks.import.post'),
        ];

        $this->app->make(PermissionRegistrar::class)->registerPermissions();
    }

    public function test_get_project_task()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/projects/tasks/' . $this->projectTask[0]->id);
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $task = $json->data;
        $this->assertNotEmpty($task);
        $this->assertEquals($this->projectTask[0]->id, $task->id);
        $this->assertEquals($this->projectTask[0]->project_id, $task->project_id);
        $this->assertEquals($this->projectTask[0]->category_id, $task->category_id);
        $this->assertEquals($this->projectTask[0]->address_id, $task->address_id);
        $this->assertEquals($this->projectTask[0]->user_id, $task->user_id);
        $this->assertEquals($this->projectTask[0]->name, $task->name);
        $this->assertEquals($this->projectTask[0]->is_paid, $task->is_paid);
        $this->assertEquals($this->projectTask[0]->is_done, $task->is_done);
        $this->assertEquals(Carbon::parse($this->projectTask[0]->created_at),
            Carbon::parse($task->created_at));
        $this->assertEquals(Carbon::parse($this->projectTask[0]->updated_at),
            Carbon::parse($task->updated_at));
    }

    public function test_get_project_task_task_not_found()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/projects/tasks/' . ++$this->projectTask[2]->id);
        $response->assertStatus(404);
    }

    public function test_get_project_tasks()
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/projects/tasks');
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $this->assertFalse(property_exists($json, 'templates'));
        $tasks = $json->data->data;
        $referenceTask = ProjectTask::query()->first();
        $this->assertNotEmpty($tasks);
        $this->assertEquals($referenceTask->id, $tasks[0]->id);
        $this->assertEquals($referenceTask->project_id, $tasks[0]->project_id);
        $this->assertEquals($referenceTask->category_id, $tasks[0]->category_id);
        $this->assertEquals($referenceTask->address_id, $tasks[0]->address_id);
        $this->assertEquals($referenceTask->user_id, $tasks[0]->user_id);
        $this->assertEquals($referenceTask->name, $tasks[0]->name);
        $this->assertEquals($referenceTask->is_paid, $tasks[0]->is_paid);
        $this->assertEquals($referenceTask->is_done, $tasks[0]->is_done);
        $this->assertEquals(Carbon::parse($referenceTask->created_at),
            Carbon::parse($tasks[0]->created_at));
        $this->assertEquals(Carbon::parse($referenceTask->updated_at),
            Carbon::parse($tasks[0]->updated_at));
    }

    public function test_create_project_task()
    {
        $projectTask = [
            'project_id' => $this->project->id,
            'category_id' => $this->projectCategories[0]->id,
            'address_id' => $this->address->id,
            'user_id' => $this->user->id,
            'name' => 'test',
        ];

        foreach ($this->additionalColumns as $additionalColumn) {
            $projectTask += [
                $additionalColumn->name => is_array($additionalColumn->values) ? $additionalColumn->values[0] : 'Value',
            ];
        }

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/projects/tasks', $projectTask);
        $response->assertStatus(201);

        $task = json_decode($response->getContent())->data;
        $dbTask = ProjectTask::query()
            ->whereKey($task->id)
            ->first();
        $this->assertNotEmpty($dbTask);
        $this->assertEquals($projectTask['project_id'], $dbTask->project_id);
        $this->assertEquals($projectTask['category_id'], $dbTask->category_id);
        $this->assertEquals($projectTask['address_id'], $dbTask->address_id);
        $this->assertEquals($projectTask['user_id'], $dbTask->user_id);
        $this->assertEquals($projectTask['name'], $dbTask->name);
        $this->assertFalse($dbTask->is_paid);
        $this->assertEquals($this->user->id, $dbTask->created_by->id);
        $this->assertEquals($this->user->id, $dbTask->updated_by->id);

        foreach ($this->additionalColumns as $additionalColumn) {
            $this->assertEquals($projectTask[$additionalColumn->name], $task->{$additionalColumn->name});
            $this->assertEquals($projectTask[$additionalColumn->name], $dbTask->{$additionalColumn->name});
        }
    }

    public function test_create_project_task_with_translation()
    {
        $languageCode = Language::factory()->create(['language_code' => 'te_st'])->language_code;

        $projectTask = [
            'project_id' => $this->project->id,
            'category_id' => $this->projectCategories[0]->id,
            'address_id' => $this->address->id,
            'user_id' => $this->user->id,
            'name' => 'test',
            'locales' => [
                $languageCode => [
                    'name' => 'Je parle pas francais',
                ],
            ],
        ];

        foreach ($this->additionalColumns as $additionalColumn) {
            $projectTask += [
                $additionalColumn->name => is_array($additionalColumn->values) ? $additionalColumn->values[0] : 'Value',
            ];
        }

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/projects/tasks', $projectTask);
        $response->assertStatus(201);

        $task = json_decode($response->getContent())->data;
        $dbTask = ProjectTask::query()
            ->whereKey($task->id)
            ->first();
        $this->assertNotEmpty($dbTask);
        $this->assertEquals($projectTask['project_id'], $dbTask->project_id);
        $this->assertEquals($projectTask['category_id'], $dbTask->category_id);
        $this->assertEquals($projectTask['address_id'], $dbTask->address_id);
        $this->assertEquals($projectTask['user_id'], $dbTask->user_id);
        $this->assertEquals($projectTask['name'], $dbTask->name);
        $this->assertEquals($projectTask['name'], $dbTask->getTranslation('name', $this->defaultLanguageCode));
        $this->assertEquals(
            $projectTask['locales'][$languageCode]['name'],
            $dbTask->getTranslation('name', $languageCode)
        );
        $this->assertFalse($dbTask->is_paid);
        $this->assertEquals($this->user->id, $dbTask->created_by->id);
        $this->assertEquals($this->user->id, $dbTask->updated_by->id);

        foreach ($this->additionalColumns as $additionalColumn) {
            $this->assertEquals($projectTask[$additionalColumn->name], $task->{$additionalColumn->name});
            $this->assertEquals($projectTask[$additionalColumn->name], $dbTask->{$additionalColumn->name});
        }
    }

    public function test_create_project_task_validation_fails()
    {
        $projectTask = [
            'project_id' => $this->project->id,
            'category_id' => $this->projectCategories[0]->id,
            'address_id' => '1234S67',
            'user_id' => $this->user->id,
            'name' => 'test',
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/projects/tasks', $projectTask);
        $response->assertStatus(422);
    }

    public function test_create_project_task_project_not_found()
    {
        $projectTask = [
            'project_id' => ++$this->project->id,
            'category_id' => $this->projectCategories[0]->id,
            'address_id' => $this->address->id,
            'user_id' => $this->user->id,
            'name' => 'test',
        ];

        foreach ($this->additionalColumns as $additionalColumn) {
            $projectTask += [
                $additionalColumn->name => is_array($additionalColumn->values) ? $additionalColumn->values[0] : 'Value',
            ];
        }

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/projects/tasks', $projectTask);
        $response->assertStatus(422);
    }

    public function test_create_project_task_category_not_found()
    {
        $this->project->categories()->detach($this->projectCategories[1]->id);

        $projectTask = [
            'project_id' => $this->project->id,
            'category_id' => $this->projectCategories[1]->id,
            'address_id' => $this->address->id,
            'user_id' => $this->user->id,
            'name' => 'test',
        ];

        foreach ($this->additionalColumns as $additionalColumn) {
            $projectTask += [
                $additionalColumn->name => is_array($additionalColumn->values) ? $additionalColumn->values[0] : 'Value',
            ];
        }

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/projects/tasks', $projectTask);
        $response->assertStatus(422);
    }

    public function test_create_project_task_address_not_found()
    {
        $projectTask = [
            'project_id' => $this->project->id,
            'category_id' => $this->projectCategories[0]->id,
            'address_id' => --$this->address->id,
            'user_id' => $this->user->id,
            'name' => 'test',
        ];

        foreach ($this->additionalColumns as $additionalColumn) {
            $projectTask += [
                $additionalColumn->name => is_array($additionalColumn->values) ? $additionalColumn->values[0] : 'Value',
            ];
        }

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/projects/tasks', $projectTask);
        $response->assertStatus(422);
    }

    public function test_create_project_task_user_not_found()
    {
        $projectTask = [
            'project_id' => $this->project->id,
            'category_id' => $this->projectCategories[0]->id,
            'address_id' => $this->address->id,
            'user_id' => ++$this->user->id,
            'name' => 'test',
        ];

        foreach ($this->additionalColumns as $additionalColumn) {
            $projectTask += [
                $additionalColumn->name => is_array($additionalColumn->values) ? $additionalColumn->values[0] : 'Value',
            ];
        }

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/projects/tasks', $projectTask);
        $response->assertStatus(422);
    }

    public function test_update_project_task()
    {
        $user = User::factory()->create(['language_id' => $this->user->language_id]);
        $projectTask = [
            'id' => $this->projectTask[0]->id,
            'category_id' => $this->projectCategories[2]->id,
            'address_id' => $this->address->id,
            'user_id' => $user->id,
            'name' => 'test',
        ];

        foreach ($this->additionalColumns as $additionalColumn) {
            $projectTask += [
                $additionalColumn->name => is_array($additionalColumn->values) ? $additionalColumn->values[0] : 'Value',
            ];
        }

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/projects/tasks', $projectTask);
        $response->assertStatus(200);

        $task = json_decode($response->getContent())->data;
        $dbTask = ProjectTask::query()
            ->whereKey($task->id)
            ->first();
        $this->assertNotEmpty($dbTask);
        $this->assertEquals($projectTask['id'], $dbTask->id);
        $this->assertEquals($projectTask['category_id'], $dbTask->category_id);
        $this->assertEquals($projectTask['address_id'], $dbTask->address_id);
        $this->assertEquals($projectTask['user_id'], $dbTask->user_id);
        $this->assertEquals($projectTask['name'], $dbTask->name);
        $this->assertEquals($this->user->id, $dbTask->updated_by->id);

        foreach ($this->additionalColumns as $additionalColumn) {
            $this->assertEquals($projectTask[$additionalColumn->name], $task->{$additionalColumn->name});
            $this->assertEquals($projectTask[$additionalColumn->name], $dbTask->{$additionalColumn->name});
        }
    }

    public function test_update_project_task_with_translation()
    {
        $user = User::factory()->create(['language_id' => $this->user->language_id]);
        $languageCode = Language::factory()->create()->language_code;

        $projectTask = [
            'id' => $this->projectTask[0]->id,
            'category_id' => $this->projectCategories[2]->id,
            'address_id' => $this->address->id,
            'user_id' => $user->id,
            'name' => 'test',
            'locales' => [
                $languageCode => [
                    'name' => 'Je parle pas francais',
                ],
            ],
        ];

        foreach ($this->additionalColumns as $additionalColumn) {
            $projectTask += [
                $additionalColumn->name => is_array($additionalColumn->values) ? $additionalColumn->values[0] : 'Value',
            ];
        }

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/projects/tasks', $projectTask);
        $response->assertStatus(200);

        $tasks = json_decode($response->getContent())->data;
        $dbTask = ProjectTask::query()
            ->whereKey($tasks->id)
            ->first();
        $this->assertNotEmpty($dbTask);
        $this->assertEquals($projectTask['id'], $dbTask->id);
        $this->assertEquals($projectTask['category_id'], $dbTask->category_id);
        $this->assertEquals($projectTask['address_id'], $dbTask->address_id);
        $this->assertEquals($projectTask['user_id'], $dbTask->user_id);
        $this->assertEquals($projectTask['name'], $dbTask->name);
        $this->assertEquals($projectTask['name'], $dbTask->getTranslation('name', $this->defaultLanguageCode));
        $this->assertEquals(
            $projectTask['locales'][$languageCode]['name'],
            $dbTask->getTranslation('name', $languageCode)
        );
        $this->assertEquals($this->user->id, $dbTask->updated_by->id);

        foreach ($this->additionalColumns as $additionalColumn) {
            $this->assertEquals($projectTask[$additionalColumn->name], $dbTask->{$additionalColumn->name});
        }
    }

    public function test_bulk_update_project_task_with_translation()
    {
        $user = User::factory()->create(['language_id' => $this->user->language_id]);
        $languageCode = Language::factory()->create()->language_code;

        $projectTasks = [
            [
                'id' => $this->projectTask[0]->id,
                'category_id' => $this->projectCategories[2]->id,
                'address_id' => $this->address->id,
                'user_id' => $user->id,
                'name' => 'test',
                'locales' => [
                    $languageCode => [
                        'name' => 'Je parle pas francais',
                    ],
                ],
            ],
            [
                'id' => $this->projectTask[1]->id,
                'category_id' => $this->projectCategories[1]->id,
                'address_id' => $this->address->id,
                'user_id' => $user->id,
                'name' => 'test',
                'locales' => [
                    $languageCode => [
                        'name' => 'Another cool language',
                    ],
                ],
            ],
        ];

        foreach ($projectTasks as $key => $projectTask) {
            foreach ($this->additionalColumns as $additionalColumn) {
                $projectTasks[$key] += [
                    $additionalColumn->name => is_array($additionalColumn->values) ? $additionalColumn->values[0] : 'Value',
                ];
            }
        }

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/projects/tasks', $projectTasks);
        $response->assertStatus(200);

        $tasks = collect(json_decode($response->getContent())->responses);
        $this->assertEquals(2, count($tasks));

        $dbTasks = ProjectTask::query()
            ->whereIn('id', $tasks->pluck('id')->toArray())
            ->get();
        $this->assertEquals(2, count($dbTasks));
        $this->assertEquals($projectTasks[0]['id'], $dbTasks[0]->id);
        $this->assertEquals($projectTasks[0]['category_id'], $dbTasks[0]->category_id);
        $this->assertEquals($projectTasks[0]['address_id'], $dbTasks[0]->address_id);
        $this->assertEquals($projectTasks[0]['user_id'], $dbTasks[0]->user_id);
        $this->assertEquals($projectTasks[0]['name'], $dbTasks[0]->name);
        $this->assertEquals($projectTasks[0]['name'], $dbTasks[0]->getTranslation('name', $this->defaultLanguageCode));
        $this->assertEquals(
            $projectTasks[0]['locales'][$languageCode]['name'],
            $dbTasks[0]->getTranslation('name', $languageCode)
        );

        $this->assertEquals($projectTasks[1]['id'], $dbTasks[1]->id);
        $this->assertEquals($projectTasks[1]['category_id'], $dbTasks[1]->category_id);
        $this->assertEquals($projectTasks[1]['address_id'], $dbTasks[1]->address_id);
        $this->assertEquals($projectTasks[1]['user_id'], $dbTasks[1]->user_id);
        $this->assertEquals($projectTasks[1]['name'], $dbTasks[1]->name);
        $this->assertEquals($projectTasks[1]['name'], $dbTasks[1]->getTranslation('name', $this->defaultLanguageCode));
        $this->assertEquals(
            $projectTasks[1]['locales'][$languageCode]['name'],
            $dbTasks[1]->getTranslation('name', $languageCode)
        );
        $this->assertEquals($this->user->id, $dbTasks[1]->updated_by->id);

        foreach ($this->additionalColumns as $additionalColumn) {
            $this->assertEquals($projectTasks[0][$additionalColumn->name], $dbTasks[0]->{$additionalColumn->name});
            $this->assertEquals($projectTasks[1][$additionalColumn->name], $dbTasks[1]->{$additionalColumn->name});
        }
    }

    public function test_bulk_update_project_tasks_with_additional_columns()
    {
        $user = User::factory()->create(['language_id' => $this->user->language_id]);
        $additionalColumns[] = AdditionalColumn::factory()->create([
            'model_type' => ProjectTask::class,
        ]);
        $additionalColumns[] = AdditionalColumn::factory()->create([
            'model_type' => ProjectTask::class,
            'values' => ['a', 'b', 'c'],
        ]);

        $projectTasks = [
            [
                'id' => $this->projectTask[0]->id,
                'category_id' => $this->projectCategories[2]->id,
                'address_id' => $this->address->id,
                'user_id' => $user->id,
                'name' => 'test',
            ],
            [
                'id' => $this->projectTask[1]->id,
                'category_id' => $this->projectCategories[1]->id,
                'address_id' => $this->address->id,
                'user_id' => $user->id,
                'name' => 'test',
            ],
        ];

        $this->additionalColumns = AdditionalColumn::query()
            ->where('model_type', ProjectTask::class)
            ->get();

        foreach ($projectTasks as $key => $projectTask) {
            foreach ($this->additionalColumns as $additionalColumn) {
                $projectTasks[$key] += [
                    $additionalColumn->name => is_array($additionalColumn->values) ? $additionalColumn->values[0] : 'Value',
                ];
            }
        }

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/projects/tasks', $projectTasks);
        $response->assertStatus(200);

        $tasks = collect(json_decode($response->getContent())->responses);
        $this->assertEquals(2, count($tasks));

        $dbTasks = ProjectTask::query()
            ->whereIn('id', $tasks->pluck('id')->toArray())
            ->get();
        $this->assertEquals(2, count($dbTasks));
        $this->assertEquals($projectTasks[0]['id'], $dbTasks[0]->id);
        $this->assertEquals($projectTasks[0]['category_id'], $dbTasks[0]->category_id);
        $this->assertEquals($projectTasks[0]['address_id'], $dbTasks[0]->address_id);
        $this->assertEquals($projectTasks[0]['user_id'], $dbTasks[0]->user_id);
        $this->assertEquals($projectTasks[0]['name'], $dbTasks[0]->name);
        $this->assertEquals($this->user->id, $dbTasks[0]->updated_by->id);

        $this->assertEquals($projectTasks[1]['id'], $dbTasks[1]->id);
        $this->assertEquals($projectTasks[1]['category_id'], $dbTasks[1]->category_id);
        $this->assertEquals($projectTasks[1]['address_id'], $dbTasks[1]->address_id);
        $this->assertEquals($projectTasks[1]['user_id'], $dbTasks[1]->user_id);
        $this->assertEquals($projectTasks[1]['name'], $dbTasks[1]->name);
        $this->assertEquals($this->user->id, $dbTasks[1]->updated_by->id);

        $this->assertEquals($projectTasks[0][$additionalColumns[0]->name], $dbTasks[0]->{$additionalColumns[0]->name});
        $this->assertEquals($projectTasks[0][$additionalColumns[1]->name], $dbTasks[0]->{$additionalColumns[1]->name});
        $this->assertEquals($projectTasks[1][$additionalColumns[0]->name], $dbTasks[1]->{$additionalColumns[0]->name});
        $this->assertEquals($projectTasks[1][$additionalColumns[1]->name], $dbTasks[1]->{$additionalColumns[1]->name});
    }

    public function test_update_project_task_validation_fails()
    {
        $projectTask = [
            'id' => $this->projectTask[0]->id,
            'category_id' => $this->projectCategories[2]->id,
            'address_id' => $this->address->id,
            'user_id' => '23A859',
            'name' => 'test',
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/projects/tasks', $projectTask);
        $response->assertStatus(422);
    }

    public function test_bulk_update_project_task_translation_validation_fails()
    {
        $languageCode = Language::factory()->create()->language_code;

        $projectTasks = [
            [
                'id' => $this->projectTask[0]->id,
                'category_id' => $this->projectCategories[2]->id,
                'address_id' => $this->address->id,
                'user_id' => $this->user->id,
                'name' => 'test',
                'locales' => [
                    $languageCode => [
                        'name' => 'Je parle pas francais',
                    ],
                ],
            ],
            [
                'id' => $this->projectTask[1]->id,
                'category_id' => $this->projectCategories[1]->id,
                'address_id' => $this->address->id,
                'user_id' => $this->user->id,
                'name' => 'test',
                'locales' => [
                    $languageCode => [
                        'name' => 42,
                    ],
                ],
            ],
        ];

        foreach ($projectTasks as $key => $projectTask) {
            foreach ($this->additionalColumns as $additionalColumn) {
                $projectTasks[$key] += [
                    $additionalColumn->name => is_array($additionalColumn->values) ? $additionalColumn->values[0] : 'Value',
                ];
            }
        }

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/projects/tasks', $projectTasks);
        $response->assertStatus(207);
        $this->assertEquals(422, json_decode($response->getContent())->responses[1]->status);
    }

    public function test_update_project_task_task_not_found()
    {
        $projectTask = [
            'id' => ++$this->projectTask[2]->id,
            'category_id' => $this->projectCategories[2]->id,
            'address_id' => $this->address->id,
            'user_id' => $this->user->id,
            'name' => 'test',
        ];

        foreach ($this->additionalColumns as $additionalColumn) {
            $projectTask += [
                $additionalColumn->name => is_array($additionalColumn->values) ? $additionalColumn->values[0] : 'Value',
            ];
        }

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/projects/tasks', $projectTask);
        $response->assertStatus(422);
    }

    public function test_update_project_task_category_not_found()
    {
        $this->project->categories()->detach($this->projectCategories[1]->id);

        $projectTask = [
            'id' => $this->projectTask[0]->id,
            'category_id' => $this->projectCategories[1]->id,
            'address_id' => $this->address->id,
            'user_id' => $this->user->id,
            'name' => 'test',
        ];

        foreach ($this->additionalColumns as $additionalColumn) {
            $projectTask += [
                $additionalColumn->name => is_array($additionalColumn->values) ? $additionalColumn->values[0] : 'Value',
            ];
        }

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/projects/tasks', $projectTask);
        $response->assertStatus(422);
    }

    public function test_update_project_task_address_not_found()
    {
        $projectTask = [
            'id' => $this->projectTask[0]->id,
            'category_id' => $this->projectCategories[2]->id,
            'address_id' => --$this->address->id,
            'user_id' => $this->user->id,
            'name' => 'test',
        ];

        foreach ($this->additionalColumns as $additionalColumn) {
            $projectTask += [
                $additionalColumn->name => is_array($additionalColumn->values) ? $additionalColumn->values[0] : 'Value',
            ];
        }

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/projects/tasks', $projectTask);
        $response->assertStatus(422);
    }

    public function test_update_project_task_user_not_found()
    {
        $projectTask = [
            'id' => $this->projectTask[0]->id,
            'category_id' => $this->projectCategories[2]->id,
            'address_id' => $this->address->id,
            'user_id' => ++$this->user->id,
            'name' => 'test',
        ];

        foreach ($this->additionalColumns as $additionalColumn) {
            $projectTask += [
                $additionalColumn->name => is_array($additionalColumn->values) ? $additionalColumn->values[0] : 'Value',
            ];
        }

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/projects/tasks', $projectTask);
        $response->assertStatus(422);
    }

    public function test_update_project_task_with_project_id()
    {
        $user = User::factory()->create(['language_id' => $this->user->language_id]);

        $projectTask = [
            'id' => $this->projectTask[2]->id,
            'project_id' => $this->project->id,
            'category_id' => $this->projectCategories[2]->id,
            'address_id' => $this->address->id,
            'user_id' => $user->id,
            'name' => 'test',
        ];

        foreach ($this->additionalColumns as $additionalColumn) {
            $projectTask += [
                $additionalColumn->name => is_array($additionalColumn->values) ? $additionalColumn->values[0] : 'Value',
            ];
        }

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/projects/tasks', $projectTask);
        $response->assertStatus(200);

        $task = json_decode($response->getContent())->data;
        $dbTask = ProjectTask::query()
            ->whereKey($task->id)
            ->first();

        $this->assertNotEmpty($dbTask);
        $this->assertEquals($projectTask['id'], $dbTask->id);
        $this->assertEquals($projectTask['project_id'], $dbTask->project_id);
        $this->assertEquals($projectTask['category_id'], $dbTask->category_id);
        $this->assertEquals($projectTask['address_id'], $dbTask->address_id);
        $this->assertEquals($projectTask['user_id'], $dbTask->user_id);
        $this->assertEquals($projectTask['name'], $dbTask->name);
        $this->assertEquals($this->user->id, $dbTask->updated_by->id);

        foreach ($this->additionalColumns as $additionalColumn) {
            $this->assertEquals($projectTask[$additionalColumn->name], $task->{$additionalColumn->name});
            $this->assertEquals($projectTask[$additionalColumn->name], $dbTask->{$additionalColumn->name});
        }
    }

    public function test_delete_project_task()
    {
        AdditionalColumn::factory()->create([
            'model_type' => ProjectTask::class,
        ]);

        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/projects/tasks/' . $this->projectTask[1]->id);
        $response->assertStatus(204);

        $projectTask = $this->projectTask[1]->fresh();
        $this->assertNotNull($projectTask->deleted_at);
        $this->assertEquals($this->user->id, $projectTask->deleted_by->id);
    }

    public function test_delete_project_task_task_not_found()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/projects/tasks/' . ++$this->projectTask[2]->id);
        $response->assertStatus(404);
    }

    public function test_finish_project_task()
    {
        AdditionalColumn::factory()->create([
            'model_type' => ProjectTask::class,
        ]);

        $projectTask = [
            'id' => $this->projectTask[0]->id,
            'finish' => true,
        ];

        $this->user->givePermissionTo($this->permissions['finish']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/projects/tasks/finish', $projectTask);
        $response->assertStatus(200);

        $task = json_decode($response->getContent())->data;
        $dbTask = ProjectTask::query()
            ->whereKey($task->id)
            ->first();
        $this->assertNotEmpty($dbTask);
        $this->assertEquals($projectTask['finish'], $dbTask->is_done);
    }

    public function test_finish_project_task_validation_fails()
    {
        $projectTask = [
            'id' => $this->projectTask[0]->id,
            'finish' => 'true',
        ];

        $this->user->givePermissionTo($this->permissions['finish']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/projects/tasks/finish', $projectTask);
        $response->assertStatus(422);
    }

    public function test_finish_project_task_task_not_found()
    {
        $projectTask = [
            'id' => ++$this->projectTask[2]->id,
            'finish' => true,
        ];

        $this->user->givePermissionTo($this->permissions['finish']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/projects/tasks/finish', $projectTask);
        $response->assertStatus(422);
    }
}
