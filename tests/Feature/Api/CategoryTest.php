<?php

use Carbon\Carbon;
use FluxErp\FluxServiceProvider;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\Address;
use FluxErp\Models\Category;
use FluxErp\Models\Contact;
use FluxErp\Models\Permission;
use FluxErp\Models\Project;
use FluxErp\Models\Task;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->categories[] = Category::factory()->create(['model_type' => morph_alias(Task::class)]);
    $this->categories[] = Category::factory()->create([
        'parent_id' => $this->categories[0]->id,
        'model_type' => morph_alias(Task::class),
    ]);

    $this->additionalColumns = AdditionalColumn::query()
        ->where('model_type', morph_alias(Category::class))
        ->get();

    $this->permissions = [
        'show' => Permission::findOrCreate('api.categories.{id}.get'),
        'index' => Permission::findOrCreate('api.categories.get'),
        'create' => Permission::findOrCreate('api.categories.post'),
        'update' => Permission::findOrCreate('api.categories.put'),
        'delete' => Permission::findOrCreate('api.categories.{id}.delete'),
    ];
});

test('create category', function (): void {
    $category = [
        'name' => 'Random Category Name',
        'model_type' => morph_alias(Task::class),
    ];

    foreach ($this->additionalColumns as $additionalColumn) {
        $category += [
            $additionalColumn->name => is_array($additionalColumn->values) ? $additionalColumn->values[0] : 'Value',
        ];
    }

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/categories', $category);
    $response->assertCreated();

    $projectCategory = json_decode($response->getContent())->data;
    $dbCategory = Category::query()
        ->whereKey($projectCategory->id)
        ->first();
    expect($dbCategory)->not->toBeEmpty();
    expect($dbCategory->parent_id)->toBeNull();
    expect($dbCategory->name)->toEqual($category['name']);
    expect($dbCategory->sort_number)->toEqual(count($this->categories) + 1);
});

test('create category additional column validation fails', function (): void {
    $additionalColumn = AdditionalColumn::factory()->create([
        'model_type' => morph_alias(Category::class),
        'values' => [0, 1, 2, 3, 4, 5],
    ]);

    $category = [
        'name' => 'Random Category Name',
        $additionalColumn->name => 23947,
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/categories', $category);
    $response->assertUnprocessable();
});

test('create category model not found', function (): void {
    $category = [
        'name' => 'Random Category Name',
        'model_type' => FluxServiceProvider::class,
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/categories', $category);
    $response->assertUnprocessable();
});

test('create category parent category not found', function (): void {
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
    $response->assertUnprocessable();
});

test('create category second validation fails', function (): void {
    $category = [
        'parent_id' => ++$this->categories[1]->id,
        'name' => 'Random Category Name',
        'model_type' => morph_alias(Task::class),
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/categories', $category);
    $response->assertUnprocessable();
});

test('create category validation fails', function (): void {
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
    $response->assertUnprocessable();
});

test('create category with additional column', function (): void {
    $additionalColumn = AdditionalColumn::factory()->create([
        'model_type' => morph_alias(Category::class),
    ]);

    $category = [
        'name' => 'Random Category Name',
        $additionalColumn->name => 'Testvalue for this column',
        'model_type' => morph_alias(Task::class),
    ];

    foreach ($this->additionalColumns as $column) {
        $category += [
            $column->name => is_array($column->values) ? $column->values[0] : 'Value',
        ];
    }

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/categories', $category);
    $response->assertCreated();

    $projectCategory = json_decode($response->getContent())->data;
    $dbCategory = Category::query()
        ->whereKey($projectCategory->id)
        ->first();

    expect($dbCategory)->not->toBeEmpty();
    expect($dbCategory->parent_id)->toBeNull();
    expect($dbCategory->name)->toEqual($category['name']);
    expect($dbCategory->sort_number)->toEqual(count($this->categories) + 1);

    expect($projectCategory->{$additionalColumn->name})->toEqual($category[$additionalColumn->name]);
    expect($dbCategory->{$additionalColumn->name})->toEqual($category[$additionalColumn->name]);
});

test('create category with additional column predefined values', function (): void {
    $additionalColumn = AdditionalColumn::factory()->create([
        'model_type' => morph_alias(Category::class),
        'values' => [0, 1, 2, 3, 4, 5],
    ]);

    $category = [
        'name' => 'Random Category Name',
        $additionalColumn->name => $additionalColumn->values[3],
        'model_type' => morph_alias(Task::class),
    ];

    foreach ($this->additionalColumns as $column) {
        $category += [
            $column->name => is_array($column->values) ? $column->values[0] : 'Value',
        ];
    }

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/categories', $category);
    $response->assertCreated();

    $projectCategory = json_decode($response->getContent())->data;
    $dbCategory = Category::query()
        ->whereKey($projectCategory->id)
        ->first();
    expect($dbCategory)->not->toBeEmpty();
    expect($dbCategory->parent_id)->toBeNull();
    expect($dbCategory->name)->toEqual($category['name']);
    expect($dbCategory->sort_number)->toEqual(count($this->categories) + 1);

    expect($projectCategory->{$additionalColumn->name})->toEqual($category[$additionalColumn->name]);
    expect($dbCategory->{$additionalColumn->name})->toEqual($category[$additionalColumn->name]);
});

test('create category with parent', function (): void {
    $category = [
        'parent_id' => $this->categories[0]->id,
        'name' => 'Random Category Name',
        'model_type' => morph_alias(Task::class),
    ];

    foreach ($this->additionalColumns as $additionalColumn) {
        $category += [
            $additionalColumn->name => is_array($additionalColumn->values) ? $additionalColumn->values[0] : 'Value',
        ];
    }

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/categories', $category);
    $response->assertCreated();

    $projectCategory = json_decode($response->getContent())->data;
    $dbCategory = Category::query()
        ->whereKey($projectCategory->id)
        ->first();
    expect($dbCategory)->not->toBeEmpty();
    expect($dbCategory->parent_id)->toEqual($category['parent_id']);
    expect($dbCategory->name)->toEqual($category['name']);
    expect($dbCategory->sort_number)->toEqual(count($this->categories) + 1);
});

test('delete category', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/categories/' . $this->categories[1]->id);
    $response->assertNoContent();

    expect(Category::query()->whereKey($this->categories[1]->id)->exists())->toBeFalse();
});

test('delete category category belongs to project', function (): void {
    $category = Category::factory()->create(['model_type' => Task::class]);
    $project = Project::factory()->create([
        'category_id' => $category->id,
        'client_id' => $this->dbClient->getKey(),
    ]);
    $contact = Contact::factory()->create([
        'client_id' => $this->dbClient->getKey(),
    ]);
    $address = Address::factory()->create([
        'client_id' => $contact->client_id,
        'contact_id' => $contact->id,
        'is_main_address' => false,
    ]);
    $projectTask = Task::factory()->create([
        'project_id' => $project->id,
        'address_id' => $address->id,
        'user_id' => $this->user->id,
    ]);
    $projectTask->category()->attach($this->categories[1]->id);

    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/categories/' . $this->categories[1]->id);
    $response->assertStatus(423);
});

test('delete category category has children', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/categories/' . $this->categories[0]->id);
    $response->assertStatus(423);
});

test('delete category category not found', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/categories/' . ++$this->categories[1]->id);
    $response->assertNotFound();
});

test('get categories', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/categories');
    $response->assertOk();

    $json = json_decode($response->getContent());
    expect(property_exists($json, 'templates'))->toBeFalse();
    $categories = $json->data->data;
    expect($categories)->not->toBeEmpty();
    $referenceCategory = Category::query()->where('id', $categories[0]->id)->first();
    expect($categories[0]->id)->toEqual($referenceCategory->id);
    expect($categories[0]->parent_id)->toEqual($referenceCategory->parent_id);
    expect($categories[0]->name)->toEqual($referenceCategory->name);
    expect($categories[0]->sort_number)->toEqual($referenceCategory->sort_number);
    expect(Carbon::parse($categories[0]->created_at))->toEqual(Carbon::parse($referenceCategory->created_at));
    expect(Carbon::parse($categories[0]->updated_at))->toEqual(Carbon::parse($referenceCategory->updated_at));
});

test('get category', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/categories/' . $this->categories[0]->id);
    $response->assertOk();

    $json = json_decode($response->getContent());
    $category = $json->data;
    expect($category)->not->toBeEmpty();
    expect($category->id)->toEqual($this->categories[0]->id);
    expect($category->parent_id)->toEqual($this->categories[0]->parent_id);
    expect($category->name)->toEqual($this->categories[0]->name);
    expect($category->sort_number)->toEqual($this->categories[0]->sort_number);
    expect(Carbon::parse($category->created_at))->toEqual(Carbon::parse($this->categories[0]->created_at));
    expect(Carbon::parse($category->updated_at))->toEqual(Carbon::parse($this->categories[0]->updated_at));
});

test('get category category not found', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/project-categories/' . ++$this->categories[1]->id);
    $response->assertNotFound();
});

test('update category', function (): void {
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
    $response->assertOk();

    $projectCategory = json_decode($response->getContent())->data;
    $dbCategory = Category::query()
        ->whereKey($projectCategory->id)
        ->first();
    expect($dbCategory)->not->toBeEmpty();
    expect($dbCategory->id)->toEqual($category['id']);
    expect($dbCategory->parent_id)->toEqual($category['parent_id']);
    expect($dbCategory->name)->toEqual($category['name']);
    expect($dbCategory->sort_number)->toEqual($category['sort_number']);
});

test('update category category not found', function (): void {
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
    $response->assertUnprocessable();
});

test('update category cycle detected', function (): void {
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
    $response->assertUnprocessable();
    expect(property_exists(json_decode($response->getContent())->errors, 'parent_id'))->toBeTrue();
});

test('update category parent category not found', function (): void {
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
    $response->assertUnprocessable();
});

test('update category parent model type not found', function (): void {
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
    $response->assertUnprocessable();
});

test('update category validation fails', function (): void {
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
    $response->assertUnprocessable();
});

test('update category with additional column', function (): void {
    $additionalColumn = AdditionalColumn::factory()->create([
        'model_type' => morph_alias(Category::class),
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
    $response->assertOk();

    $projectCategory = json_decode($response->getContent())->data;
    $dbCategory = Category::query()
        ->whereKey($projectCategory->id)
        ->first();
    expect($dbCategory)->not->toBeEmpty();
    expect($dbCategory->id)->toEqual($category['id']);
    expect($dbCategory->parent_id)->toEqual($category['parent_id']);
    expect($dbCategory->name)->toEqual($category['name']);
    expect($dbCategory->sort_number)->toEqual($category['sort_number']);

    expect($projectCategory->{$additionalColumn->name})->toEqual($category[$additionalColumn->name]);
    expect($dbCategory->{$additionalColumn->name})->toEqual($category[$additionalColumn->name]);
});
