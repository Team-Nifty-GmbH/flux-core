<?php

namespace FluxErp\Tests\Feature;

use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\Address;
use FluxErp\Models\Category;
use FluxErp\Models\Contact;
use FluxErp\Models\Permission;
use FluxErp\Models\Project;
use FluxErp\Models\ProjectCategoryTemplate;
use FluxErp\Models\ProjectTask;
use FluxErp\Services\CategoryService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;

class CategoryTest extends BaseSetup
{
    use DatabaseTransactions;

    private Collection $additionalColumns;

    private array $categories;

    private array $permissions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->categories[] = Category::factory()->create(['model_type' => ProjectTask::class]);
        $this->categories[] = Category::factory()->create([
            'parent_id' => $this->categories[0]->id,
            'model_type' => ProjectTask::class,
        ]);

        $this->additionalColumns = AdditionalColumn::query()
            ->where('model_type', Category::class)
            ->get();

        $this->permissions = [
            'show' => Permission::findOrCreate('api.categories.{id}.get'),
            'index' => Permission::findOrCreate('api.categories.get'),
            'create' => Permission::findOrCreate('api.categories.post'),
            'update' => Permission::findOrCreate('api.categories.put'),
            'delete' => Permission::findOrCreate('api.categories.{id}.delete'),
        ];

        $this->app->make(PermissionRegistrar::class)->registerPermissions();
    }

    public function test_get_category()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/categories/' . $this->categories[0]->id);
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $category = $json->data;
        $this->assertNotEmpty($category);
        $this->assertEquals($this->categories[0]->id, $category->id);
        $this->assertEquals($this->categories[0]->parent_id, $category->parent_id);
        $this->assertEquals($this->categories[0]->name, $category->name);
        $this->assertEquals($this->categories[0]->sort_number, $category->sort_number);
        $this->assertEquals(Carbon::parse($this->categories[0]->created_at),
            Carbon::parse($category->created_at));
        $this->assertEquals(Carbon::parse($this->categories[0]->updated_at),
            Carbon::parse($category->updated_at));
    }

    public function test_get_category_category_not_found()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/project-categories/' . ++$this->categories[1]->id);
        $response->assertStatus(404);
    }

    public function test_get_categories()
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/categories');
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $this->assertFalse(property_exists($json, 'templates'));
        $categories = $json->data->data;
        $this->assertNotEmpty($categories);
        $referenceCategory = Category::query()->where('id', $categories[0]->id)->first();
        $this->assertEquals($referenceCategory->id, $categories[0]->id);
        $this->assertEquals($referenceCategory->parent_id, $categories[0]->parent_id);
        $this->assertEquals($referenceCategory->name, $categories[0]->name);
        $this->assertEquals($referenceCategory->sort_number, $categories[0]->sort_number);
        $this->assertEquals(Carbon::parse($referenceCategory->created_at),
            Carbon::parse($categories[0]->created_at));
        $this->assertEquals(Carbon::parse($referenceCategory->updated_at),
            Carbon::parse($categories[0]->updated_at));
    }

    public function test_create_category()
    {
        $category = [
            'name' => 'Random Category Name',
            'model_type' => ProjectTask::class,
        ];

        foreach ($this->additionalColumns as $additionalColumn) {
            $category += [
                $additionalColumn->name => is_array($additionalColumn->values) ? $additionalColumn->values[0] : 'Value',
            ];
        }

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/categories', $category);
        $response->assertStatus(201);

        $projectCategory = json_decode($response->getContent())->data;
        $dbCategory = Category::query()
            ->whereKey($projectCategory->id)
            ->first();
        $this->assertNotEmpty($dbCategory);
        $this->assertNull($dbCategory->parent_id);
        $this->assertEquals($category['name'], $dbCategory->name);
        $this->assertEquals(0, $dbCategory->sort_number);
    }

    public function test_create_category_with_parent()
    {
        $category = [
            'parent_id' => $this->categories[0]->id,
            'name' => 'Random Category Name',
            'model_type' => ProjectTask::class,
        ];

        foreach ($this->additionalColumns as $additionalColumn) {
            $category += [
                $additionalColumn->name => is_array($additionalColumn->values) ? $additionalColumn->values[0] : 'Value',
            ];
        }

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/categories', $category);
        $response->assertStatus(201);

        $projectCategory = json_decode($response->getContent())->data;
        $dbCategory = Category::query()
            ->whereKey($projectCategory->id)
            ->first();
        $this->assertNotEmpty($dbCategory);
        $this->assertEquals($category['parent_id'], $dbCategory->parent_id);
        $this->assertEquals($category['name'], $dbCategory->name);
        $this->assertEquals(0, $dbCategory->sort_number);
    }

    public function test_create_category_with_additional_column()
    {
        $additionalColumn = AdditionalColumn::factory()->create([
            'model_type' => Category::class,
        ]);

        $category = [
            'name' => 'Random Category Name',
            $additionalColumn->name => 'Testvalue for this column',
            'model_type' => ProjectTask::class,
        ];

        foreach ($this->additionalColumns as $column) {
            $category += [
                $column->name => is_array($column->values) ? $column->values[0] : 'Value',
            ];
        }

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/categories', $category);
        $response->assertStatus(201);

        $projectCategory = json_decode($response->getContent())->data;
        $dbCategory = Category::query()
            ->whereKey($projectCategory->id)
            ->first();

        $this->assertNotEmpty($dbCategory);
        $this->assertNull($dbCategory->parent_id);
        $this->assertEquals($category['name'], $dbCategory->name);
        $this->assertEquals(0, $dbCategory->sort_number);

        $this->assertEquals($category[$additionalColumn->name], $projectCategory->{$additionalColumn->name});
        $this->assertEquals($category[$additionalColumn->name], $dbCategory->{$additionalColumn->name});
    }

    public function test_create_category_with_additional_column_predefined_values()
    {
        $additionalColumn = AdditionalColumn::factory()->create([
            'model_type' => Category::class,
            'values' => [0, 1, 2, 3, 4, 5],
        ]);

        $category = [
            'name' => 'Random Category Name',
            $additionalColumn->name => $additionalColumn->values[3],
            'model_type' => ProjectTask::class,
        ];

        foreach ($this->additionalColumns as $column) {
            $category += [
                $column->name => is_array($column->values) ? $column->values[0] : 'Value',
            ];
        }

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/categories', $category);
        $response->assertStatus(201);

        $projectCategory = json_decode($response->getContent())->data;
        $dbCategory = Category::query()
            ->whereKey($projectCategory->id)
            ->first();
        $this->assertNotEmpty($dbCategory);
        $this->assertNull($dbCategory->parent_id);
        $this->assertEquals($category['name'], $dbCategory->name);
        $this->assertEquals(0, $dbCategory->sort_number);

        $this->assertEquals($category[$additionalColumn->name], $projectCategory->{$additionalColumn->name});
        $this->assertEquals($category[$additionalColumn->name], $dbCategory->{$additionalColumn->name});
    }

    public function test_create_category_validation_fails()
    {
        $category = [
            'name' => 12345,
        ];

        foreach ($this->additionalColumns as $additionalColumn) {
            $category += [
                $additionalColumn->name => is_array($additionalColumn->values) ? $additionalColumn->values[0] : 'Value',
            ];
        }

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/categories', $category);
        $response->assertStatus(422);
    }

    public function test_create_category_additional_column_validation_fails()
    {
        $additionalColumn = AdditionalColumn::factory()->create([
            'model_type' => Category::class,
            'values' => [0, 1, 2, 3, 4, 5],
        ]);

        $category = [
            'name' => 'Random Category Name',
            $additionalColumn->name => 23947,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/categories', $category);
        $response->assertStatus(422);
    }

    public function test_create_category_parent_category_not_found()
    {
        $category = [
            'parent_id' => ++$this->categories[1]->id,
            'name' => 'Random Category Name',
        ];

        foreach ($this->additionalColumns as $additionalColumn) {
            $category += [
                $additionalColumn->name => is_array($additionalColumn->values) ? $additionalColumn->values[0] : 'Value',
            ];
        }

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/categories', $category);
        $response->assertStatus(422);
    }

    public function test_create_category_second_level_category()
    {
        $category = [
            'parent_id' => $this->categories[1]->id,
            'name' => 'Second level Category',
            'model_type' => ProjectTask::class,
        ];

        foreach ($this->additionalColumns as $additionalColumn) {
            $category += [
                $additionalColumn->name => is_array($additionalColumn->values) ? $additionalColumn->values[0] : 'Value',
            ];
        }

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/categories', $category);
        $response->assertStatus(409);
    }

    public function test_create_category_model_not_found()
    {
        $category = [
            'name' => 'Random Category Name',
            'model_type' => CategoryService::class,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/categories', $category);
        $response->assertStatus(404);
    }

    public function test_create_category_second_validation_fails()
    {
        $category = [
            'parent_id' => ++$this->categories[1]->id,
            'name' => 'Random Category Name',
            'model_type' => ProjectTask::class,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/categories', $category);
        $response->assertStatus(422);
    }

    public function test_update_category()
    {
        $category = [
            'id' => $this->categories[1]->id,
            'parent_id' => null,
            'name' => 'Random Category Name',
            'sort_number' => 1,
        ];

        foreach ($this->additionalColumns as $additionalColumn) {
            $category += [
                $additionalColumn->name => is_array($additionalColumn->values) ? $additionalColumn->values[0] : 'Value',
            ];
        }

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/categories', $category);
        $response->assertStatus(200);

        $projectCategory = json_decode($response->getContent())->data;
        $dbCategory = Category::query()
            ->whereKey($projectCategory->id)
            ->first();
        $this->assertNotEmpty($dbCategory);
        $this->assertEquals($category['id'], $dbCategory->id);
        $this->assertEquals($category['parent_id'], $dbCategory->parent_id);
        $this->assertEquals($category['name'], $dbCategory->name);
        $this->assertEquals($category['sort_number'], $dbCategory->sort_number);
    }

    public function test_update_category_with_additional_column()
    {
        $additionalColumn = AdditionalColumn::factory()->create([
            'model_type' => Category::class,
        ]);

        $this->categories[1]->saveMeta($additionalColumn->name, 'Original Value');

        $category = [
            'id' => $this->categories[1]->id,
            'parent_id' => null,
            'name' => 'Random Category Name',
            'sort_number' => 1,
            $additionalColumn->name => 'Testvalue for this column',
        ];

        foreach ($this->additionalColumns as $column) {
            $category += [
                $column->name => is_array($column->values) ? $column->values[0] : 'Value',
            ];
        }

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/categories', $category);
        $response->assertStatus(200);

        $projectCategory = json_decode($response->getContent())->data;
        $dbCategory = Category::query()
            ->whereKey($projectCategory->id)
            ->first();
        $this->assertNotEmpty($dbCategory);
        $this->assertEquals($category['id'], $dbCategory->id);
        $this->assertEquals($category['parent_id'], $dbCategory->parent_id);
        $this->assertEquals($category['name'], $dbCategory->name);
        $this->assertEquals($category['sort_number'], $dbCategory->sort_number);

        $this->assertEquals($category[$additionalColumn->name], $projectCategory->{$additionalColumn->name});
        $this->assertEquals($category[$additionalColumn->name], $dbCategory->{$additionalColumn->name});
    }

    public function test_update_category_validation_fails()
    {
        $category = [
            'id' => $this->categories[1]->id,
            'parent_id' => 'null',
            'name' => 'Random Category Name',
            'sort_number' => 1,
        ];

        foreach ($this->additionalColumns as $additionalColumn) {
            $category += [
                $additionalColumn->name => is_array($additionalColumn->values) ? $additionalColumn->values[0] : 'Value',
            ];
        }

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/categories', $category);
        $response->assertStatus(422);
    }

    public function test_update_category_category_not_found()
    {
        $category = [
            'id' => ++$this->categories[1]->id,
            'name' => $this->categories[1]->name,
        ];

        foreach ($this->additionalColumns as $additionalColumn) {
            $category += [
                $additionalColumn->name => is_array($additionalColumn->values) ? $additionalColumn->values[0] : 'Value',
            ];
        }

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/categories', $category);
        $response->assertStatus(422);
    }

    public function test_update_category_parent_category_not_found()
    {
        $category = [
            'id' => $this->categories[1]->id,
            'parent_id' => ++$this->categories[1]->id,
            'name' => $this->categories[1]->name,
        ];

        foreach ($this->additionalColumns as $additionalColumn) {
            $category += [
                $additionalColumn->name => is_array($additionalColumn->values) ? $additionalColumn->values[0] : 'Value',
            ];
        }

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/categories', $category);
        $response->assertStatus(422);
    }

    public function test_update_category_parent_model_type_not_found()
    {
        $newCategory = Category::factory()->create(['model_type' => 'ProjectTask']);
        $category = [
            'id' => $newCategory->id,
            'parent_id' => $this->categories[1]->id,
            'name' => $this->categories[1]->name,
            'model_type' => 'ProjectTask',
        ];

        foreach ($this->additionalColumns as $additionalColumn) {
            $category += [
                $additionalColumn->name => is_array($additionalColumn->values) ? $additionalColumn->values[0] : 'Value',
            ];
        }

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/categories', $category);
        $response->assertStatus(422);
    }

    public function test_update_category_second_level_category()
    {
        $newCategory = Category::factory()->create(['model_type' => ProjectTask::class]);
        $category = [
            'id' => $newCategory->id,
            'parent_id' => $this->categories[1]->id,
            'name' => $this->categories[1]->name,
            'model_type' => ProjectTask::class,
        ];

        foreach ($this->additionalColumns as $additionalColumn) {
            $category += [
                $additionalColumn->name => is_array($additionalColumn->values) ? $additionalColumn->values[0] : 'Value',
            ];
        }

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/categories', $category);
        $response->assertStatus(409);
        $this->assertTrue(
            property_exists(json_decode($response->getContent())->errors, 'parent_id')
        );
    }

    public function test_update_category_cycle_detected()
    {
        $category = [
            'id' => $this->categories[0]->id,
            'parent_id' => $this->categories[0]->id,
            'name' => $this->categories[0]->name,
        ];

        foreach ($this->additionalColumns as $additionalColumn) {
            $category += [
                $additionalColumn->name => is_array($additionalColumn->values) ? $additionalColumn->values[0] : 'Value',
            ];
        }

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/categories', $category);
        $response->assertStatus(409);
        $this->assertTrue(
            property_exists(json_decode($response->getContent())->errors, 'parent_id')
        );
    }

    public function test_delete_category()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/categories/' . $this->categories[1]->id);
        $response->assertStatus(204);

        $this->assertFalse(Category::query()->whereKey($this->categories[1]->id)->exists());
    }

    public function test_delete_category_category_not_found()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/categories/' . ++$this->categories[1]->id);
        $response->assertStatus(404);
    }

    public function test_delete_category_category_has_children()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/categories/' . $this->categories[0]->id);
        $response->assertStatus(423);
    }

    public function test_delete_category_category_belongs_to_project()
    {
        $projectCategoryTemplate = ProjectCategoryTemplate::factory()->create();
        $projectCategoryTemplate->categories()->attach($this->categories[1]->id);
        $project = Project::factory()->create(['project_category_template_id' => $projectCategoryTemplate->id]);
        $contact = Contact::factory()->create([
            'client_id' => $this->dbClient->id,
        ]);
        $address = Address::factory()->create([
            'client_id' => $contact->client_id,
            'contact_id' => $contact->id,
            'is_main_address' => false,
        ]);
        $projectTask = ProjectTask::factory()->create([
            'project_id' => $project->id,
            'address_id' => $address->id,
            'user_id' => $this->user->id,
        ]);
        $projectTask->category()->attach($this->categories[1]->id);

        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/categories/' . $this->categories[1]->id);
        $response->assertStatus(423);
    }
}
