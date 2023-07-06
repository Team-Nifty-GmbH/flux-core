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
use FluxErp\Models\ProjectCategoryTemplate;
use FluxErp\Models\ProjectTask;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;

class ProjectTest extends BaseSetup
{
    use DatabaseTransactions;

    private Collection $projects;

    private Model $projectCategoryTemplate;

    private Collection $categories;

    private array $permissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->projectCategoryTemplate = ProjectCategoryTemplate::factory()->create();
        $this->projects = Project::factory()->count(2)->create([
            'project_category_template_id' => $this->projectCategoryTemplate->id,
        ]);

        $this->categories = Category::factory()->count(2)->create(['model_type' => ProjectTask::class]);
        $this->projectCategoryTemplate->categories()->attach($this->categories->pluck('id')->toArray());

        $this->permissions = [
            'show' => Permission::findOrCreate('api.projects.{id}.get'),
            'index' => Permission::findOrCreate('api.projects.get'),
            'create' => Permission::findOrCreate('api.projects.post'),
            'update' => Permission::findOrCreate('api.projects.put'),
            'delete' => Permission::findOrCreate('api.projects.{id}.delete'),
            'finish' => Permission::findOrCreate('api.projects.finish.post'),
            'statistics' => Permission::findOrCreate('api.projects.{id}.statistics.get'),
        ];

        $this->app->make(PermissionRegistrar::class)->registerPermissions();
    }

    public function test_get_project()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/projects/' . $this->projects[0]->id);
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $project = $json->data;
        $this->assertNotEmpty($project);
        $this->assertEquals($this->projects[0]->id, $project->id);
        $this->assertEquals($this->projects[0]->parent_id, $project->parent_id);
        $this->assertEquals($this->projects[0]->project_category_template_id, $project->project_category_template_id);
        $this->assertEquals($this->projects[0]->project_name, $project->project_name);
        $this->assertEquals($this->projects[0]->display_name, $project->display_name);
        $this->assertEquals(Carbon::parse($this->projects[0]->release_date)->toDateString(), $project->release_date);
        $this->assertNull($project->deadline);
        $this->assertEquals($this->projects[0]->description, $project->description);
        $this->assertEquals($this->projects[0]->is_done, $project->is_done);
        $this->assertEquals(Carbon::parse($this->projects[0]->created_at),
            Carbon::parse($project->created_at));
        $this->assertEquals(Carbon::parse($this->projects[0]->updated_at),
            Carbon::parse($project->updated_at));
    }

    public function test_get_project_project_not_found()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/projects/' . ++$this->projects[1]->id);
        $response->assertStatus(404);
    }

    public function test_get_projects()
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/projects');
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $this->assertFalse(property_exists($json, 'templates'));
        $projects = $json->data->data;
        $referenceProject = Project::query()->first();
        $this->assertNotEmpty($projects);
        $this->assertEquals($referenceProject->id, $projects[0]->id);
        $this->assertEquals($referenceProject->parent_id, $projects[0]->parent_id);
        $this->assertEquals($referenceProject->project_category_template_id, $projects[0]->project_category_template_id);
        $this->assertEquals($referenceProject->project_name, $projects[0]->project_name);
        $this->assertEquals($referenceProject->display_name, $projects[0]->display_name);
        $this->assertEquals(Carbon::parse($referenceProject->release_date)->toDateString(), $projects[0]->release_date);
        $this->assertEquals($referenceProject->deadline ?
            Carbon::parse($referenceProject->deadline)->toDateString() : null, $projects[0]->deadline);
        $this->assertEquals($referenceProject->description, $projects[0]->description);
        $this->assertEquals($referenceProject->is_done, $projects[0]->is_done);
        $this->assertEquals(Carbon::parse($referenceProject->created_at), Carbon::parse($projects[0]->created_at));
        $this->assertEquals(Carbon::parse($referenceProject->updated_at), Carbon::parse($projects[0]->updated_at));
    }

    public function test_create_project()
    {
        $project = [
            'parent_id' => $this->projects[0]->id,
            'project_category_template_id' => $this->projectCategoryTemplate->id,
            'project_name' => 'Project Name',
            'display_name' => 'Display Name',
            'release_date' => date('Y-m-d'),
            'deadline' => date('Y-m-t'),
            'description' => 'New description text for further information',
            'categories' => $this->categories->pluck('id')->toArray(),
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/projects', $project);
        $response->assertStatus(201);

        $responseProject = json_decode($response->getContent())->data;
        $dbProject = Project::query()
            ->whereKey($responseProject->id)
            ->first();
        $this->assertNotEmpty($dbProject);
        $this->assertEquals($project['parent_id'], $dbProject->parent_id);
        $this->assertEquals($project['project_category_template_id'], $dbProject->project_category_template_id);
        $this->assertEquals($project['project_name'], $dbProject->project_name);
        $this->assertEquals($project['display_name'], $dbProject->display_name);
        $this->assertEquals($project['release_date'], Carbon::parse($dbProject->release_date)->toDateString());
        $this->assertEquals($project['deadline'], Carbon::parse($dbProject->deadline)->toDateString());
        $this->assertEquals($project['description'], $dbProject->description);
        $this->assertEquals($this->user->id, $dbProject->created_by->id);
        $this->assertEquals($this->user->id, $dbProject->updated_by->id);
    }

    public function test_create_project_with_translations()
    {
        $languageCode = Language::factory()->create()->language_code;

        $project = [
            'parent_id' => $this->projects[0]->id,
            'project_category_template_id' => $this->projectCategoryTemplate->id,
            'project_name' => 'Project Name',
            'display_name' => 'Display Name',
            'release_date' => date('Y-m-d'),
            'deadline' => date('Y-m-t'),
            'description' => 'New description text for further information',
            'categories' => $this->categories->pluck('id')->toArray(),
            'locales' => [
                $languageCode => [
                    'project_name' => 'Je parle pas francais',
                    'display_name' => 'Tous le monde',
                ],
            ],
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/projects', $project);
        $response->assertStatus(201);

        $responseProject = json_decode($response->getContent())->data;
        $dbProject = Project::query()
            ->whereKey($responseProject->id)
            ->first();
        $this->assertNotEmpty($dbProject);
        $this->assertEquals($project['parent_id'], $dbProject->parent_id);
        $this->assertEquals($project['project_category_template_id'], $dbProject->project_category_template_id);
        $this->assertEquals($project['project_name'], $dbProject->project_name);
        $this->assertEquals($project['project_name'],
            $dbProject->getTranslation('project_name', $this->defaultLanguageCode));
        $this->assertEquals($project['locales'][$languageCode]['project_name'],
            $dbProject->getTranslation('project_name', $languageCode));
        $this->assertEquals($project['display_name'], $dbProject->display_name);
        $this->assertEquals($project['display_name'],
            $dbProject->getTranslation('display_name', $this->defaultLanguageCode));
        $this->assertEquals($project['locales'][$languageCode]['display_name'],
            $dbProject->getTranslation('display_name', $languageCode));
        $this->assertEquals($project['release_date'], $dbProject->release_date);
        $this->assertEquals($project['deadline'], $dbProject->deadline);
        $this->assertEquals($project['description'], $dbProject->description);
        $this->assertEquals($this->user->id, $dbProject->created_by->id);
        $this->assertEquals($this->user->id, $dbProject->updated_by->id);
    }

    public function test_create_project_validation_fails()
    {
        $project = [
            'parent_id' => $this->projects[0]->id,
            'project_category_template_id' => $this->projectCategoryTemplate->id,
            'project_name' => 'Project Name',
            'display_name' => 'Display Name',
            'release_date' => date('Y-m-D'),
            'deadline' => date('Y-m-t'),
            'description' => 'New description text for further information',
            'categories' => [],
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/projects', $project);
        $response->assertStatus(422);
    }

    public function test_create_project_parent_project_not_found()
    {
        $project = [
            'parent_id' => ++$this->projects[1]->id,
            'project_category_template_id' => $this->projectCategoryTemplate->id,
            'project_name' => 'Project Name',
            'release_date' => date('Y-m-d'),
            'categories' => $this->categories->pluck('id')->toArray(),
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/projects', $project);
        $response->assertStatus(422);
    }

    public function test_create_project_second_level_project()
    {
        $parent = Project::factory()->create([
            'project_category_template_id' => $this->projectCategoryTemplate->id,
            'parent_id' => $this->projects[0]->id,
        ]);

        $project = [
            'parent_id' => $parent->id,
            'project_category_template_id' => $this->projectCategoryTemplate->id,
            'project_name' => 'Project Name',
            'release_date' => date('Y-m-d'),
            'categories' => $this->categories->pluck('id')->toArray(),
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/projects', $project);
        $response->assertStatus(422);
    }

    public function test_create_project_category_template_not_found()
    {
        $project = [
            'parent_id' => $this->projects[1]->id,
            'project_category_template_id' => ++$this->projectCategoryTemplate->id,
            'project_name' => 'Project Name',
            'release_date' => date('Y-m-d'),
            'categories' => $this->categories->pluck('id')->toArray(),
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/projects', $project);
        $response->assertStatus(422);
    }

    public function test_create_project_categories_not_found()
    {
        $category = Category::factory()->create(['model_type' => ProjectTask::class]);
        $project = [
            'parent_id' => $this->projects[1]->id,
            'project_category_template_id' => $this->projectCategoryTemplate->id,
            'project_name' => 'Project Name',
            'release_date' => date('Y-m-d'),
            'categories' => array_merge($this->categories->pluck('id')->toArray(), [$category->id]),
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/projects', $project);
        $response->assertStatus(422);
    }

    public function test_update_project()
    {
        $project = [
            'id' => $this->projects[0]->id,
            'project_category_template_id' => $this->projectCategoryTemplate->id,
            'project_name' => 'Project Name',
            'display_name' => null,
            'release_date' => date('Y-m-d'),
            'description' => 'New description text for further information',
            'deadline' => null,
            'categories' => $this->categories->pluck('id')->toArray(),
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/projects', $project);
        $response->assertStatus(200);

        $responseProject = json_decode($response->getContent())->data;
        $dbProject = (object) Project::query()
            ->whereKey($responseProject->id)
            ->first()
            ->append(['created_by', 'updated_by'])
            ->toArray();

        $this->assertNotEmpty($dbProject);
        $this->assertEquals($project['id'], $dbProject->id);
        $this->assertEquals($project['project_category_template_id'], $dbProject->project_category_template_id);
        $this->assertEquals($project['project_name'], $dbProject->project_name);
        $this->assertNull($dbProject->display_name);
        $this->assertEquals($project['release_date'], Carbon::parse($dbProject->release_date)->toDateString());
        $this->assertNull($dbProject->deadline);
        $this->assertEquals($project['description'], $dbProject->description);
        $this->assertEquals($this->user->id, $dbProject->updated_by['id']);
    }

    public function test_update_project_with_additional_column()
    {
        $additionalColumns = AdditionalColumn::factory()->count(2)->create([
            'model_type' => Project::class,
        ]);

        $value = 'Original value from second additional column';
        $this->projects[0]->saveMeta($additionalColumns[0]->name, 'Original Value');
        $this->projects[0]->saveMeta($additionalColumns[1]->name, $value);

        $categoryTemplateId = $this->projects[0]->project_category_template_id;

        $project = [
            'id' => $this->projects[0]->id,
            'project_name' => 'Project Name',
            'display_name' => null,
            'release_date' => date('Y-m-d'),
            'description' => 'New description text for further information',
            'deadline' => null,
            $additionalColumns[0]->name => 'New Value',
            $additionalColumns[1]->name => null,
            'categories' => $this->categories->pluck('id')->toArray(),
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/projects', $project);
        $response->assertStatus(200);

        $responseProject = json_decode($response->getContent())->data;
        $dbProject = (object) Project::query()
            ->whereKey($responseProject->id)
            ->first()
            ->append(['created_by', 'updated_by'])
            ->toArray();

        $this->assertNotEmpty($dbProject);
        $this->assertEquals($project['id'], $dbProject->id);
        $this->assertEquals($categoryTemplateId, $dbProject->project_category_template_id);
        $this->assertEquals($project['project_name'], $dbProject->project_name);
        $this->assertNull($dbProject->display_name);
        $this->assertEquals($project['release_date'], Carbon::parse($dbProject->release_date)->toDateString());
        $this->assertNull($dbProject->deadline);
        $this->assertEquals($project['description'], $dbProject->description);
        $this->assertEquals($this->user->id, $dbProject->updated_by['id']);
        $this->assertEquals($project[$additionalColumns[0]->name], $responseProject->{$additionalColumns[0]->name});
        $this->assertEquals($project[$additionalColumns[0]->name], $dbProject->{$additionalColumns[0]->name});
        $this->assertNull($responseProject->{$additionalColumns[1]->name});
        $this->assertNull($dbProject->{$additionalColumns[1]->name});
    }

    public function test_update_project_with_translations()
    {
        $languageCode = Language::factory()->create()->language_code;

        $project = [
            'id' => $this->projects[0]->id,
            'project_category_template_id' => $this->projectCategoryTemplate->id,
            'project_name' => 'Project Name',
            'release_date' => date('Y-m-d'),
            'description' => 'New description text for further information',
            'locales' => [
                $languageCode => [
                    'project_name' => 'Je parle pas francais',
                    'display_name' => 'Tous le monde',
                ],
            ],
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/projects', $project);
        $response->assertStatus(200);

        $responseProject = json_decode($response->getContent())->data;
        $dbProject = Project::query()
            ->whereKey($responseProject->id)
            ->first();
        $this->assertNotEmpty($dbProject);
        $this->assertEquals($project['id'], $dbProject->id);
        $this->assertEquals($project['project_category_template_id'], $dbProject->project_category_template_id);
        $this->assertEquals($project['project_name'], $dbProject->project_name);
        $this->assertEquals($project['project_name'],
            $dbProject->getTranslation('project_name', $this->defaultLanguageCode));
        $this->assertEquals($project['locales'][$languageCode]['project_name'],
            $dbProject->getTranslation('project_name', $languageCode));
        $this->assertEquals($this->projects[0]->display_name, $dbProject->display_name);
        $this->assertEquals($project['locales'][$languageCode]['display_name'],
            $dbProject->getTranslation('display_name', $languageCode));
        $this->assertEquals($project['release_date'], $dbProject->release_date);
        $this->assertNull($dbProject->deadline);
        $this->assertEquals($project['description'], $dbProject->description);
        $this->assertEquals($this->user->id, $dbProject->updated_by->id);
    }

    public function test_update_project_validation_fails()
    {
        $project = [
            'id' => $this->projects[0]->id,
            'project_category_template_id' => $this->projectCategoryTemplate->id,
            'project_name' => 'Project Name',
            'release_date' => date('Y-m-d'),
            'deadline' => date('Y-m-D'),
            'categories' => [],
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/projects', $project);
        $response->assertStatus(422);
    }

    public function test_update_project_project_not_found()
    {
        $project = [
            'id' => ++$this->projects[1]->id,
            'project_category_template_id' => $this->projectCategoryTemplate->id,
            'project_name' => 'Project Name',
            'release_date' => date('Y-m-d'),
            'categories' => $this->categories->pluck('id')->toArray(),
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/projects', $project);
        $response->assertStatus(422);
    }

    public function test_update_project_category_template_not_found()
    {
        $project = [
            'id' => $this->projects[1]->id,
            'project_category_template_id' => ++$this->projectCategoryTemplate->id,
            'project_name' => 'Project Name',
            'release_date' => date('Y-m-d'),
            'categories' => $this->categories->pluck('id')->toArray(),
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/projects', $project);
        $response->assertStatus(422);
    }

    public function test_update_project_categories_not_found()
    {
        $category = Category::factory()->create(['model_type' => ProjectTask::class]);
        $project = [
            'id' => $this->projects[1]->id,
            'project_category_template_id' => $this->projectCategoryTemplate->id,
            'project_name' => 'Project Name',
            'release_date' => date('Y-m-d'),
            'categories' => array_merge($this->categories->pluck('id')->toArray(), [$category->id]),
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/projects', $project);
        $response->assertStatus(422);
        $this->assertTrue(
            property_exists(json_decode($response->getContent())->errors, 'categories')
        );
    }

    public function test_update_project_project_task_category_differs()
    {
        $categories = $this->categories->pluck('id')->toArray();
        $contact = Contact::factory()->create(['client_id' => $this->dbClient->id]);
        $address = Address::factory()->create(['contact_id' => $contact->id, 'client_id' => $contact->client_id]);
        $projectTask = ProjectTask::factory()->create([
            'project_id' => $this->projects[1]->id,
            'address_id' => $address->id,
            'user_id' => $this->user->id,
        ]);
        $projectTask->category()->attach(array_pop($categories));

        $project = [
            'id' => $this->projects[1]->id,
            'project_category_template_id' => $this->projectCategoryTemplate->id,
            'project_name' => 'Project Name',
            'release_date' => date('Y-m-d'),
            'categories' => $categories,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/projects', $project);
        $response->assertStatus(422);
        $this->assertTrue(
            property_exists(json_decode($response->getContent())->errors, 'categories')
        );
    }

    public function test_update_project_categories_validation_fails()
    {
        $project = [
            'id' => $this->projects[0]->id,
            'project_category_template_id' => $this->projectCategoryTemplate->id,
            'project_name' => 'Project Name',
            'display_name' => null,
            'release_date' => date('Y-m-d'),
            'description' => 'New description text for further information',
            'deadline' => null,
            'categories' => [Str::random()],
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/projects', $project);
        $response->assertStatus(422);
    }

    public function test_delete_project()
    {
        AdditionalColumn::factory()->create([
            'model_type' => Project::class,
        ]);

        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/projects/' . $this->projects[1]->id);
        $response->assertStatus(204);

        $project = $this->projects[1]->fresh();
        $this->assertNotNull($project->deleted_at);
        $this->assertEquals($this->user->id, $project->deleted_by->id);
    }

    public function test_delete_project_project_not_found()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/projects/' . ++$this->projects[1]->id);
        $response->assertStatus(404);
    }

    public function test_delete_project_project_has_children()
    {
        $this->projects[0]->parent_id = $this->projects[1]->id;
        $this->projects[0]->save();

        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/projects/' . $this->projects[1]->id);
        $response->assertStatus(423);
    }

    public function test_finish_project()
    {
        AdditionalColumn::factory()->create([
            'model_type' => Project::class,
        ]);

        $project = [
            'id' => $this->projects[1]->id,
            'finish' => true,
        ];

        $this->user->givePermissionTo($this->permissions['finish']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/projects/finish', $project);
        $response->assertStatus(200);

        $responseProject = json_decode($response->getContent())->data;
        $dbProject = Project::query()
            ->whereKey($responseProject->id)
            ->first();
        $this->assertNotEmpty($dbProject);
        $this->assertEquals($project['id'], $dbProject->id);
        $this->assertTrue($dbProject->is_done);
    }

    public function test_reopen_project()
    {
        $project = [
            'id' => $this->projects[1]->id,
            'finish' => false,
        ];

        $this->user->givePermissionTo($this->permissions['finish']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/projects/finish', $project);
        $response->assertStatus(200);

        $responseProject = json_decode($response->getContent())->data;
        $dbProject = Project::query()
            ->whereKey($responseProject->id)
            ->first();
        $this->assertNotEmpty($dbProject);
        $this->assertEquals($project['id'], $dbProject->id);
        $this->assertFalse($dbProject->is_done);
    }

    public function test_finish_project_validation_fails()
    {
        $project = [
            'id' => $this->projects[1]->id,
            'finish' => 'True',
        ];

        $this->user->givePermissionTo($this->permissions['finish']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/projects/finish', $project);
        $response->assertStatus(422);
    }

    public function test_finish_project_project_not_found()
    {
        $project = [
            'id' => ++$this->projects[1]->id,
            'finish' => true,
        ];

        $this->user->givePermissionTo($this->permissions['finish']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/projects/finish', $project);
        $response->assertStatus(422);
    }
}
