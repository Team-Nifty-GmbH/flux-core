<?php

namespace FluxErp\Tests\Feature\Api;

use Carbon\Carbon;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\Category;
use FluxErp\Models\Permission;
use FluxErp\Models\User;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

class AdditionalColumnsTest extends BaseSetup
{
    private array $additionalColumns;

    private array $permissions;

    protected function setUp(): void
    {
        parent::setUp();
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
    }

    public function test_create_additional_column_model_not_found(): void
    {
        $additionalColumn = [
            'name' => 'hopefullyNeverExistingName' . Str::random(),
            'model_type' => 'user' . Str::random(),
            'values' => ['test', 1, 3, 'c', 'g'],
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/additional-columns', $additionalColumn);
        $response->assertStatus(422);
    }

    public function test_create_additional_column_name_model_combination_already_exists(): void
    {
        $additionalColumn = [
            'name' => $this->additionalColumns[0]->name,
            'model_type' => $this->additionalColumns[0]->model_type,
            'values' => ['test', 1, 3, 'c', 'g'],
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/additional-columns', $additionalColumn);
        $response->assertStatus(422);
    }

    public function test_create_additional_column_validation_fails(): void
    {
        $additionalColumn = [
            'name' => 'hopefullyNeverExistingName' . Str::random(),
            'model_type' => 'user',
            'values' => [],
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/additional-columns', $additionalColumn);
        $response->assertStatus(422);
    }

    public function test_create_additional_column_values_no_list(): void
    {
        $additionalColumn = [
            'name' => 'hopefullyNeverExistingName' . Str::random(),
            'model_type' => 'user',
            'values' => ['test' => 1, 3 => 'c', 'g'],
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/additional-columns', $additionalColumn);
        $response->assertStatus(422);
    }

    public function test_delete_additional_column(): void
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/additional-columns/' . $this->additionalColumns[2]->id);
        $response->assertStatus(204);

        $this->assertEmpty($this->additionalColumns[2]->fresh());
    }

    public function test_get_additional_column_additional_column_not_found(): void
    {
        $additionalColumn = AdditionalColumn::factory()->create([
            'model_type' => mb_ord(User::class),
        ]);

        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/additional-columns/' . $additionalColumn->id + 1);
        $response->assertStatus(404);
    }

    public function test_get_additional_columns(): void
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/additional-columns');
        $response->assertStatus(200);

        $jsonAdditionalColumns = collect(json_decode($response->getContent())->data->data);
        foreach ($this->additionalColumns as $additionalColumn) {
            $this->assertTrue(
                $jsonAdditionalColumns
                    ->contains(function ($jsonAdditionalColumn) use ($additionalColumn) {
                        return $jsonAdditionalColumn->id === $additionalColumn->id &&
                            $jsonAdditionalColumn->name === $additionalColumn->name &&
                            $jsonAdditionalColumn->model_type === $additionalColumn->model_type &&
                            $jsonAdditionalColumn->values === $additionalColumn->values &&
                            Carbon::parse($jsonAdditionalColumn->created_at)->toDateTimeString() ===
                            Carbon::parse($additionalColumn->created_at)->timezone('GMT')->toDateTimeString() &&
                            Carbon::parse($jsonAdditionalColumn->updated_at)->toDateTimeString() ===
                            Carbon::parse($additionalColumn->created_at)->timezone('GMT')->toDateTimeString();
                    })
            );
        }
    }

    public function test_get_additional_columns_by_model(): void
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $queryParams = '?filter[model_type]=user';
        $response = $this->actingAs($this->user)->get('/api/additional-columns' . $queryParams);
        $response->assertStatus(200);

        $jsonAdditionalColumns = collect(json_decode($response->getContent())->data->data);

        $this->assertNotEmpty($jsonAdditionalColumns);
        $this->assertTrue($jsonAdditionalColumns->every(function ($value, $key) {
            return $value->model_type === morph_alias(User::class);
        }));

        $additionalColumn = $this->additionalColumns[0];
        $this->assertTrue($jsonAdditionalColumns->contains(function ($jsonAdditionalColumn) use ($additionalColumn) {
            return $jsonAdditionalColumn->id === $additionalColumn->id &&
                $jsonAdditionalColumn->name === $additionalColumn->name &&
                $jsonAdditionalColumn->model_type === $additionalColumn->model_type &&
                $jsonAdditionalColumn->values === $additionalColumn->values &&
                Carbon::parse($jsonAdditionalColumn->created_at)->toDateTimeString() ===
                Carbon::parse($additionalColumn->created_at)->timezone('GMT')->toDateTimeString() &&
                Carbon::parse($jsonAdditionalColumn->updated_at)->toDateTimeString() ===
                Carbon::parse($additionalColumn->created_at)->timezone('GMT')->toDateTimeString();
        }));

        $additionalColumn = $this->additionalColumns[2];
        $this->assertTrue($jsonAdditionalColumns->contains(function ($jsonAdditionalColumn) use ($additionalColumn) {
            return $jsonAdditionalColumn->id === $additionalColumn->id &&
                $jsonAdditionalColumn->name === $additionalColumn->name &&
                $jsonAdditionalColumn->model_type === $additionalColumn->model_type &&
                $jsonAdditionalColumn->values === $additionalColumn->values &&
                Carbon::parse($jsonAdditionalColumn->created_at)->toDateTimeString() ===
                Carbon::parse($additionalColumn->created_at)->timezone('GMT')->toDateTimeString() &&
                Carbon::parse($jsonAdditionalColumn->updated_at)->toDateTimeString() ===
                Carbon::parse($additionalColumn->created_at)->timezone('GMT')->toDateTimeString();
        }));
    }

    public function test_get_additional_columns_model_not_found(): void
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $queryParams = '?filter[model_type]=zywx' . Str::random();
        $response = $this->actingAs($this->user)->get('/api/additional-columns' . $queryParams);
        $response->assertStatus(200);
        $this->assertEmpty(json_decode($response->getContent())->data->data);
    }

    public function test_update_additional_column(): void
    {
        $additionalColumn = [
            'id' => $this->additionalColumns[0]->id,
            'name' => 'hopefullyNeverExistingName' . Str::random(),
            'values' => ['test', 1, 3, 'c', 'g'],
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/additional-columns', $additionalColumn);
        $response->assertStatus(200);

        $jsonAdditionalColumn = json_decode($response->getContent())->data;
        $dbAdditionalColumn = $this->additionalColumns[0]->fresh();

        $this->assertEquals($additionalColumn['id'], $jsonAdditionalColumn->id);
        $this->assertEquals($additionalColumn['id'], $dbAdditionalColumn->id);
        $this->assertEquals($additionalColumn['name'], $dbAdditionalColumn->name);
        $this->assertEquals($additionalColumn['values'], $dbAdditionalColumn->values);
        $this->assertEquals($this->additionalColumns[0]->model_type, $dbAdditionalColumn->model_type);
    }

    public function test_update_additional_column_model_has_values_exists(): void
    {
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
        $response->assertStatus(422);
    }

    public function test_update_additional_column_validation_fails(): void
    {
        $additionalColumn = [
            'id' => $this->additionalColumns[0]->id,
            'name' => '',
            'values' => [],
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/additional-columns', $additionalColumn);
        $response->assertStatus(422);
    }

    public function test_update_additional_column_values_no_list(): void
    {
        $additionalColumn = [
            'id' => $this->additionalColumns[0]->id,
            'values' => ['test' => 1, 3 => 'c', 'g'],
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/additional-columns', $additionalColumn);
        $response->assertStatus(422);
    }
}
