<?php

use Carbon\Carbon;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\Category;
use FluxErp\Models\Permission;
use FluxErp\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->additionalColumns[] = AdditionalColumn::factory()->create([
        'model_type' => morph_alias(User::class),
        'values' => [1, 2, 3, 4, 5],
    ]);
    $this->additionalColumns[] = AdditionalColumn::factory()->create([
        'model_type' => morph_alias(Category::class),
        'values' => [1, 3, 5, 7],
    ]);
    $this->additionalColumns[] = AdditionalColumn::factory()->create([
        'model_type' => morph_alias(User::class),
        'values' => [1, 1, 2, 3, 5, 8],
    ]);

    $this->permissions = [
        'show' => Permission::findOrCreate('api.additional-columns.{id}.get'),
        'index' => Permission::findOrCreate('api.additional-columns.get'),
        'create' => Permission::findOrCreate('api.additional-columns.post'),
        'update' => Permission::findOrCreate('api.additional-columns.put'),
        'delete' => Permission::findOrCreate('api.additional-columns.{id}.delete'),
    ];
});

test('create additional column model not found', function (): void {
    $additionalColumn = [
        'name' => 'hopefullyNeverExistingName' . Str::random(),
        'model_type' => 'user' . Str::random(),
        'values' => ['test', 1, 3, 'c', 'g'],
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/additional-columns', $additionalColumn);
    $response->assertUnprocessable();
});

test('create additional column name model combination already exists', function (): void {
    $additionalColumn = [
        'name' => $this->additionalColumns[0]->name,
        'model_type' => $this->additionalColumns[0]->model_type,
        'values' => ['test', 1, 3, 'c', 'g'],
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/additional-columns', $additionalColumn);
    $response->assertUnprocessable();
});

test('create additional column validation fails', function (): void {
    $additionalColumn = [
        'name' => 'hopefullyNeverExistingName' . Str::random(),
        'model_type' => 'user',
        'values' => [],
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/additional-columns', $additionalColumn);
    $response->assertUnprocessable();
});

test('create additional column values no list', function (): void {
    $additionalColumn = [
        'name' => 'hopefullyNeverExistingName' . Str::random(),
        'model_type' => 'user',
        'values' => ['test' => 1, 3 => 'c', 'g'],
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/additional-columns', $additionalColumn);
    $response->assertUnprocessable();
});

test('delete additional column', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/additional-columns/' . $this->additionalColumns[2]->id);
    $response->assertNoContent();

    expect($this->additionalColumns[2]->fresh())->toBeEmpty();
});

test('get additional column additional column not found', function (): void {
    $additionalColumn = AdditionalColumn::factory()->create([
        'model_type' => mb_ord(User::class),
    ]);

    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/additional-columns/' . $additionalColumn->id + 1);
    $response->assertNotFound();
});

test('get additional columns', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/additional-columns');
    $response->assertOk();

    $jsonAdditionalColumns = collect(json_decode($response->getContent())->data->data);
    foreach ($this->additionalColumns as $additionalColumn) {
        expect($jsonAdditionalColumns
            ->contains(function ($jsonAdditionalColumn) use ($additionalColumn) {
                return $jsonAdditionalColumn->id === $additionalColumn->id &&
                    $jsonAdditionalColumn->name === $additionalColumn->name &&
                    $jsonAdditionalColumn->model_type === $additionalColumn->model_type &&
                    $jsonAdditionalColumn->values === $additionalColumn->values &&
                    Carbon::parse($jsonAdditionalColumn->created_at)->toDateTimeString() ===
                    Carbon::parse($additionalColumn->created_at)->timezone('GMT')->toDateTimeString() &&
                    Carbon::parse($jsonAdditionalColumn->updated_at)->toDateTimeString() ===
                    Carbon::parse($additionalColumn->created_at)->timezone('GMT')->toDateTimeString();
            }))->toBeTrue();
    }
});

test('get additional columns by model', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $queryParams = '?filter[model_type]=user';
    $response = $this->actingAs($this->user)->get('/api/additional-columns' . $queryParams);
    $response->assertOk();

    $jsonAdditionalColumns = collect(json_decode($response->getContent())->data->data);

    expect($jsonAdditionalColumns)->not->toBeEmpty();
    expect($jsonAdditionalColumns->every(function ($value, $key) {
        return $value->model_type === morph_alias(User::class);
    }))->toBeTrue();

    $additionalColumn = $this->additionalColumns[0];
    expect($jsonAdditionalColumns->contains(function ($jsonAdditionalColumn) use ($additionalColumn) {
        return $jsonAdditionalColumn->id === $additionalColumn->id &&
            $jsonAdditionalColumn->name === $additionalColumn->name &&
            $jsonAdditionalColumn->model_type === $additionalColumn->model_type &&
            $jsonAdditionalColumn->values === $additionalColumn->values &&
            Carbon::parse($jsonAdditionalColumn->created_at)->toDateTimeString() ===
            Carbon::parse($additionalColumn->created_at)->timezone('GMT')->toDateTimeString() &&
            Carbon::parse($jsonAdditionalColumn->updated_at)->toDateTimeString() ===
            Carbon::parse($additionalColumn->created_at)->timezone('GMT')->toDateTimeString();
    }))->toBeTrue();

    $additionalColumn = $this->additionalColumns[2];
    expect($jsonAdditionalColumns->contains(function ($jsonAdditionalColumn) use ($additionalColumn) {
        return $jsonAdditionalColumn->id === $additionalColumn->id &&
            $jsonAdditionalColumn->name === $additionalColumn->name &&
            $jsonAdditionalColumn->model_type === $additionalColumn->model_type &&
            $jsonAdditionalColumn->values === $additionalColumn->values &&
            Carbon::parse($jsonAdditionalColumn->created_at)->toDateTimeString() ===
            Carbon::parse($additionalColumn->created_at)->timezone('GMT')->toDateTimeString() &&
            Carbon::parse($jsonAdditionalColumn->updated_at)->toDateTimeString() ===
            Carbon::parse($additionalColumn->created_at)->timezone('GMT')->toDateTimeString();
    }))->toBeTrue();
});

test('get additional columns model not found', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $queryParams = '?filter[model_type]=zywx' . Str::random();
    $response = $this->actingAs($this->user)->get('/api/additional-columns' . $queryParams);
    $response->assertOk();
    expect(json_decode($response->getContent())->data->data)->toBeEmpty();
});

test('update additional column', function (): void {
    $additionalColumn = [
        'id' => $this->additionalColumns[0]->id,
        'name' => 'hopefullyNeverExistingName' . Str::random(),
        'values' => ['test', 1, 3, 'c', 'g'],
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/additional-columns', $additionalColumn);
    $response->assertOk();

    $jsonAdditionalColumn = json_decode($response->getContent())->data;
    $dbAdditionalColumn = $this->additionalColumns[0]->fresh();

    expect($jsonAdditionalColumn->id)->toEqual($additionalColumn['id']);
    expect($dbAdditionalColumn->id)->toEqual($additionalColumn['id']);
    expect($dbAdditionalColumn->name)->toEqual($additionalColumn['name']);
    expect($dbAdditionalColumn->values)->toEqual($additionalColumn['values']);
    expect($dbAdditionalColumn->model_type)->toEqual($this->additionalColumns[0]->model_type);
});

test('update additional column model has values exists', function (): void {
    $additionalColumn = [
        'id' => $this->additionalColumns[1]->id,
        'name' => 'hopefullyNeverExistingName' . Str::random(),
        'values' => ['test', 1, 3, 'c', 'g'],
    ];

    $category = Category::factory()->create(['model_type' => Model::class]);

    $category->saveMeta($this->additionalColumns[1]->name, array_pop($additionalColumn['values']));

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/additional-columns', $additionalColumn);
    $response->assertUnprocessable();
});

test('update additional column validation fails', function (): void {
    $additionalColumn = [
        'id' => $this->additionalColumns[0]->id,
        'name' => '',
        'values' => [],
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/additional-columns', $additionalColumn);
    $response->assertUnprocessable();
});

test('update additional column values no list', function (): void {
    $additionalColumn = [
        'id' => $this->additionalColumns[0]->id,
        'values' => ['test' => 1, 3 => 'c', 'g'],
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/additional-columns', $additionalColumn);
    $response->assertUnprocessable();
});
