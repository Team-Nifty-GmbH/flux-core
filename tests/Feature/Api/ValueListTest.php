<?php

uses(FluxErp\Tests\Feature\BaseSetup::class);
use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\Category;
use FluxErp\Models\Permission;
use FluxErp\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->valueLists[] = AdditionalColumn::factory()->create([
        'model_type' => morph_alias(User::class),
        'values' => [1, 2, 3, 4, 5],
    ]);
    $this->valueLists[] = AdditionalColumn::factory()->create([
        'model_type' => morph_alias(Category::class),
        'values' => [1, 3, 5, 7],
    ]);
    $this->valueLists[] = AdditionalColumn::factory()->create([
        'model_type' => morph_alias(User::class),
        'values' => [1, 1, 2, 3, 5, 8],
    ]);

    $this->permissions = [
        'show' => Permission::findOrCreate('api.value-lists.{id}.get'),
        'index' => Permission::findOrCreate('api.value-lists.get'),
        'create' => Permission::findOrCreate('api.value-lists.post'),
        'update' => Permission::findOrCreate('api.value-lists.put'),
        'delete' => Permission::findOrCreate('api.value-lists.{id}.delete'),
    ];
});

test('create value list', function (): void {
    $valueList = [
        'name' => 'hopefullyNeverExistingName' . Str::random(),
        'model_type' => morph_alias(Category::class),
        'values' => ['test', 1, 3, 'c', 'g'],
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/value-lists', $valueList);
    $response->assertStatus(201);

    $jsonValueList = json_decode($response->getContent())->data;
    $dbValueList = AdditionalColumn::query()
        ->whereKey($jsonValueList->id)
        ->first();

    expect($dbValueList)->not->toBeEmpty();
    expect($dbValueList->name)->toEqual($valueList['name']);
    expect($dbValueList->model_type)->toEqual($valueList['model_type']);
    expect($dbValueList->values)->toEqual($valueList['values']);
});

test('delete value list model has values', function (): void {
    $category = Category::factory()->create(['model_type' => Model::class]);
    $category->saveMeta($this->valueLists[1]->name, $this->valueLists[1]->values[0]);

    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/value-lists/' . $this->valueLists[1]->id);
    $response->assertStatus(423);
});

test('delete value list value list not found', function (): void {
    $valueList = AdditionalColumn::factory()->create([
        'model_type' => morph_alias(User::class),
    ]);

    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/value-lists/' . $valueList->id);
    $response->assertStatus(422);
});

test('update value list name model combination already exists', function (): void {
    $valueList = [
        'id' => $this->valueLists[0]->id,
        'name' => $this->valueLists[2]->name,
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/value-lists', $valueList);
    $response->assertStatus(422);
});
