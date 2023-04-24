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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;

class ProjectCategoryTemplateTest extends BaseSetup
{
    use DatabaseTransactions;

    private Collection $categories;

    private Model $template;

    private array $permissions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->categories = Category::factory()->count(3)->create(['model_type' => ProjectTask::class]);
        $this->template = ProjectCategoryTemplate::factory()->create();

        $this->permissions = [
            'show' => Permission::findOrCreate('api.project-categories.templates.{id}.get'),
            'index' => Permission::findOrCreate('api.project-categories.templates.get'),
            'create' => Permission::findOrCreate('api.project-categories.templates.post'),
            'update' => Permission::findOrCreate('api.project-categories.templates.put'),
            'delete' => Permission::findOrCreate('api.project-categories.templates.{id}.delete'),
        ];

        $this->app->make(PermissionRegistrar::class)->registerPermissions();
    }

    public function test_get_template()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/project-categories/templates/' . $this->template->id);
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $template = $json->data;
        $this->assertNotEmpty($template);
        $this->assertEquals($this->template->id, $template->id);
        $this->assertEquals($this->template->name, $template->name);
        $this->assertEquals(Carbon::parse($this->template->created_at),
            Carbon::parse($template->created_at));
        $this->assertEquals(Carbon::parse($this->template->updated_at),
            Carbon::parse($template->updated_at));
    }

    public function test_get_template_with_categories()
    {
        $this->categories[1]->parent_id = $this->categories[0]->id;
        $this->categories[1]->save();
        $this->template->categories()->sync($this->categories);

        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $queryParams = '?include=categories';
        $response = $this->actingAs($this->user)
            ->get('/api/project-categories/templates/' . $this->template->id . $queryParams);
        $response->assertStatus(200);
        $json = json_decode($response->getContent());
        $template = $json->data;
        $this->assertNotEmpty($template);
        $this->assertEquals($this->template->id, $template->id);
        $this->assertEquals($this->template->name, $template->name);
        $this->assertEquals(Carbon::parse($this->template->created_at),
            Carbon::parse($template->created_at));
        $this->assertEquals(Carbon::parse($this->template->updated_at),
            Carbon::parse($template->updated_at));
        $this->assertEquals($this->categories[0]->id, $template->categories[0]->id);
        $this->assertEquals($this->categories[1]->id, $template->categories[1]->id);
        $this->assertEquals($this->categories[2]->id, $template->categories[2]->id);
    }

    public function test_get_template_template_not_found()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/project-categories/templates/' . ++$this->template->id);
        $response->assertStatus(404);
    }

    public function test_get_templates()
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/project-categories/templates');
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $this->assertFalse(property_exists($json, 'templates'));
        $templates = $json->data->data;
        $referenceTemplate = ProjectCategoryTemplate::query()->first();
        $this->assertNotEmpty($templates);
        $this->assertEquals($referenceTemplate->id, $templates[0]->id);
        $this->assertEquals($referenceTemplate->name, $templates[0]->name);
        $this->assertEquals(Carbon::parse($referenceTemplate->created_at),
            Carbon::parse($templates[0]->created_at));
        $this->assertEquals(Carbon::parse($referenceTemplate->updated_at),
            Carbon::parse($templates[0]->updated_at));
    }

    public function test_get_templates_with_categories()
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $queryParams = '?include=categories';
        $response = $this->actingAs($this->user)->get('/api/project-categories/templates' . $queryParams);
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $this->assertFalse(property_exists($json, 'templates'));
        $templates = $json->data->data;
        $this->assertTrue(property_exists($templates[0], 'categories'));
        $referenceTemplate = ProjectCategoryTemplate::query()->first();
        $this->assertNotEmpty($templates);
        $this->assertEquals($referenceTemplate->id, $templates[0]->id);
        $this->assertEquals($referenceTemplate->name, $templates[0]->name);
        $this->assertEquals(Carbon::parse($referenceTemplate->created_at),
            Carbon::parse($templates[0]->created_at));
        $this->assertEquals(Carbon::parse($referenceTemplate->updated_at),
            Carbon::parse($templates[0]->updated_at));
        $this->assertEmpty($templates[count($templates) - 1]->categories);
    }

    public function test_get_templates_templates_not_searchable()
    {
        $queryParams = '?search=' . Str::random();

        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)
            ->get('/api/project-categories/templates/' . $queryParams);

        $response->assertStatus(400);
    }

    public function test_create_template()
    {
        $template = [
            'name' => 'Template Name, better if not exists beforehand',
            'categories' => $this->categories->pluck('id')->toArray(),
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/project-categories/templates', $template);
        $response->assertStatus(201);

        $categoryTemplate = json_decode($response->getContent())->data;
        $dbTemplate = ProjectCategoryTemplate::query()
            ->whereKey($categoryTemplate->id)
            ->first();
        $this->assertNotEmpty($dbTemplate);
        $this->assertEquals($template['name'], $dbTemplate->name);
        $this->assertEquals($template['categories'], $dbTemplate->categories()->get()->pluck('id')->toArray());
    }

    public function test_create_template_with_translation()
    {
        $languageCode = Language::factory()->create()->language_code;

        $template = [
            'name' => 'Template Name, better if not exists beforehand',
            'categories' => $this->categories->pluck('id')->toArray(),
            'locales' => [
                $languageCode => [
                    'name' => 'Je parle pas francais',
                ],
            ],
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/project-categories/templates', $template);
        $response->assertStatus(201);

        $categoryTemplate = json_decode($response->getContent())->data;
        $dbTemplate = ProjectCategoryTemplate::query()
            ->whereKey($categoryTemplate->id)
            ->first();
        $this->assertNotEmpty($dbTemplate);
        $this->assertEquals($template['name'], $dbTemplate->name);
        $this->assertEquals($template['name'], $dbTemplate->getTranslation('name', $this->defaultLanguageCode));
        $this->assertEquals($template['locales'][$languageCode]['name'],
            $dbTemplate->getTranslation('name', $languageCode));
        $this->assertEquals($template['categories'], $dbTemplate->categories()->get()->pluck('id')->toArray());
    }

    public function test_create_template_with_additional_column_translation()
    {
        $languageCode = Language::factory()->create()->language_code;
        $additionalColumn = AdditionalColumn::factory()->create([
            'model_type' => ProjectCategoryTemplate::class,
            'is_translatable' => true,
        ]);

        Cache::store('array')->forget('meta_casts_' . ProjectCategoryTemplate::class);
        Cache::store('array')->forget('meta_translatable_' . ProjectCategoryTemplate::class);

        $template = [
            'name' => 'Template Name, better if not exists beforehand',
            'categories' => $this->categories->pluck('id')->toArray(),
            'locales' => [
                $languageCode => [
                    $additionalColumn->name => 'Je parle pas francais',
                    'name' => 'Nom du modèle, préférable s il n existe pas auparavant',
                ],
            ],
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/project-categories/templates', $template);
        $response->assertStatus(201);

        $categoryTemplate = json_decode($response->getContent())->data;
        $dbTemplate = ProjectCategoryTemplate::query()
            ->whereKey($categoryTemplate->id)
            ->first();

        $this->assertNotEmpty($dbTemplate);
        $this->assertEquals($template['name'], $dbTemplate->name);
        $this->assertEquals($template['categories'], $dbTemplate->categories()->get()->pluck('id')->toArray());
        app()->setLocale($languageCode);
        $this->assertEquals(
            $template['locales'][$languageCode][$additionalColumn->name],
            $dbTemplate->{$additionalColumn->name}
        );
    }

    public function test_create_template_validation_fails()
    {
        $template = [
            'name' => 123456,
            'categories' => [],
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/project-categories/templates', $template);
        $response->assertStatus(422);
    }

    public function test_create_template_category_not_found()
    {
        $template = [
            'name' => 'Template Name, better if not exists beforehand',
            'categories' => [
                'bla', '4', -3, 0, $this->categories[0]->id, ++$this->categories[2]->id,
            ],
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/project-categories/templates', $template);
        $response->assertStatus(422);
    }

    public function test_update_template()
    {
        $template = [
            'id' => $this->template->id,
            'name' => 'Template Name, better if not exists beforehand',
            'categories' => $this->categories->toArray(),
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/project-categories/templates', $template);
        $response->assertStatus(200);

        $categoryTemplate = json_decode($response->getContent());
        $dbTemplate = ProjectCategoryTemplate::query()
            ->whereKey($categoryTemplate->id)
            ->first();
        $this->assertNotEmpty($dbTemplate);
        $this->assertEquals($template['id'], $dbTemplate->id);
        $this->assertEquals($template['name'], $dbTemplate->name);
        $categories = $dbTemplate->categories()->get()->pluck('id')->toArray();
        $this->assertEquals(3, count($categories));
        $this->assertEquals($categories[0], $this->categories[0]->id);
        $this->assertEquals($categories[1], $this->categories[1]->id);
        $this->assertEquals($categories[2], $this->categories[2]->id);
    }

    public function test_update_template_with_additional_column()
    {
        $additionalColumn = AdditionalColumn::factory()->create([
            'model_type' => ProjectCategoryTemplate::class,
        ]);

        $this->template->saveMeta($additionalColumn->name, 'Original Value');

        $template = [
            'id' => $this->template->id,
            'name' => 'Template Name, better if not exists beforehand',
            'categories' => $this->categories->toArray(),
            $additionalColumn->name => 'New Value',
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/project-categories/templates', $template);
        $response->assertStatus(200);

        $categoryTemplate = json_decode($response->getContent())->data;
        $dbTemplate = ProjectCategoryTemplate::query()
            ->whereKey($categoryTemplate->id)
            ->first();
        $this->assertNotEmpty($dbTemplate);
        $this->assertEquals($template['id'], $dbTemplate->id);
        $this->assertEquals($template['name'], $dbTemplate->name);
        $categories = $dbTemplate->categories()->get()->pluck('id')->toArray();
        $this->assertEquals(3, count($categories));
        $this->assertEquals($categories[0], $this->categories[0]->id);
        $this->assertEquals($categories[1], $this->categories[1]->id);
        $this->assertEquals($categories[2], $this->categories[2]->id);

        $this->assertEquals($template[$additionalColumn->name], $categoryTemplate->{$additionalColumn->name});
        $this->assertEquals($template[$additionalColumn->name], $dbTemplate->{$additionalColumn->name});
    }

    public function test_update_template_with_translation()
    {
        $languageCode = Language::factory()->create()->language_code;

        $template = [
            'id' => $this->template->id,
            'name' => 'Template Name, better if not exists beforehand',
            'categories' => $this->categories->toArray(),
            'locales' => [
                $languageCode => [
                    'name' => 'Je parle pas francais',
                ],
            ],
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/project-categories/templates', $template);
        $response->assertStatus(200);

        $categoryTemplate = json_decode($response->getContent())->data;
        $dbTemplate = ProjectCategoryTemplate::query()
            ->whereKey($categoryTemplate->id)
            ->first();
        $this->assertNotEmpty($dbTemplate);
        $this->assertEquals($template['id'], $dbTemplate->id);
        $this->assertEquals($template['name'], $dbTemplate->name);
        $this->assertEquals($template['name'], $dbTemplate->getTranslation('name', $this->defaultLanguageCode));
        $this->assertEquals($template['locales'][$languageCode]['name'],
            $dbTemplate->getTranslation('name', $languageCode));
        $categories = $dbTemplate->categories()->get()->pluck('id')->toArray();
        $this->assertEquals(3, count($categories));
        $this->assertEquals($categories[0], $this->categories[0]->id);
        $this->assertEquals($categories[1], $this->categories[1]->id);
        $this->assertEquals($categories[2], $this->categories[2]->id);
    }

    public function test_update_template_validation_fails()
    {
        $template = [
            'id' => 'ID',
            'name' => 123456,
            'categories' => [],
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/project-categories/templates', $template);
        $response->assertStatus(422);
    }

    public function test_update_template_template_not_found()
    {
        $template = [
            'id' => ++$this->template->id,
            'name' => 'Template Name, better if not exists beforehand',
            'categories' => [
                $this->categories[0]->id, $this->categories[2]->id,
            ],
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/project-categories/templates', $template);
        $response->assertStatus(422);
    }

    public function test_update_template_category_not_found()
    {
        $template = [
            'id' => $this->template->id,
            'name' => 'Template Name, better if not exists beforehand',
            'categories' => [
                'bla', '4', -3, 0, $this->categories[0]->id, ++$this->categories[2]->id,
            ],
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/project-categories/templates', $template);
        $response->assertStatus(422);
        $this->assertEquals(
            count($template['categories']) - 1,
            count(json_decode($response->getContent(), true))
        );
    }

    public function test_update_template_category_referenced_by_project_task()
    {
        $project = Project::factory()->create(['project_category_template_id' => $this->template->id]);
        $contact = Contact::factory()->create(['client_id' => $this->dbClient->id]);
        $address = Address::factory()->create(['contact_id' => $contact->id, 'client_id' => $contact->client_id]);
        $projectTask = ProjectTask::factory()->create([
            'project_id' => $project->id,
            'address_id' => $address->id,
            'user_id' => $this->user->id,
        ]);
        $projectTask->categories()->attach($this->categories[1]->id);

        $this->template->categories()->sync($this->categories);

        $template = [
            'id' => $this->template->id,
            'name' => 'Template Name, better if not exists beforehand',
            'categories' => [
                $this->categories[0]->id, $this->categories[2]->id,
            ],
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/project-categories/templates', $template);
        $response->assertStatus(423);
        $this->assertTrue(
            property_exists(json_decode($response->getContent())->errors, 'categories')
        );
    }

    public function test_update_template_without_categories()
    {
        $template = [
            'id' => $this->template->id,
            'name' => 'Template Name, better if not exists beforehand',
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/project-categories/templates', $template);
        $response->assertStatus(200);

        $categoryTemplate = json_decode($response->getContent());
        $dbTemplate = ProjectCategoryTemplate::query()
            ->whereKey($categoryTemplate->id)
            ->first();
        $this->assertNotEmpty($dbTemplate);
        $this->assertEquals($template['id'], $dbTemplate->id);
        $this->assertEquals($template['name'], $dbTemplate->name);
    }

    public function test_delete_template()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/project-categories/templates/' . $this->template->id);
        $response->assertStatus(204);
    }

    public function test_delete_template_template_not_found()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/project-categories/templates/' . ++$this->template->id);
        $response->assertStatus(404);
    }

    public function test_delete_template_referenced_by_project()
    {
        Project::factory()->create([
            'project_category_template_id' => $this->template->id,
        ]);

        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/project-categories/templates/' . $this->template->id);
        $response->assertStatus(423);
    }

    public function test_delete_template_referenced_by_soft_deleted_project()
    {
        $project = Project::factory()->create([
            'project_category_template_id' => $this->template->id,
        ]);
        $project->delete();

        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/project-categories/templates/' . $this->template->id);
        $response->assertStatus(423);
    }
}
