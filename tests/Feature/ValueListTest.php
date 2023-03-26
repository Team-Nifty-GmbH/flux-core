<?php

namespace FluxErp\Tests\Feature;

use FluxErp\Helpers\Helper;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\Category;
use FluxErp\Models\Permission;
use FluxErp\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;

class ValueListTest extends BaseSetup
{
    use DatabaseTransactions;

    private array $valueLists;

    private array $permissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->valueLists[] = AdditionalColumn::factory()->create([
            'model_type' => User::class,
            'values' => [1, 2, 3, 4, 5],
        ]);
        $this->valueLists[] = AdditionalColumn::factory()->create([
            'model_type' => Category::class,
            'values' => [1, 3, 5, 7],
        ]);
        $this->valueLists[] = AdditionalColumn::factory()->create([
            'model_type' => User::class,
            'values' => [1, 1, 2, 3, 5, 8],
        ]);

        $this->permissions = [
            'show' => Permission::findOrCreate('api.value-lists.{id}.get'),
            'index' => Permission::findOrCreate('api.value-lists.get'),
            'create' => Permission::findOrCreate('api.value-lists.post'),
            'update' => Permission::findOrCreate('api.value-lists.put'),
            'delete' => Permission::findOrCreate('api.value-lists.{id}.delete'),
        ];

        $this->app->make(PermissionRegistrar::class)->registerPermissions();
    }

    public function test_get_value_list()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/value-lists/' . $this->valueLists[0]->id);
        $response->assertStatus(200);

        $jsonValueList = json_decode($response->getContent())->data;
        $this->assertNotEmpty($jsonValueList);
        $this->assertEquals($this->valueLists[0]->id, $jsonValueList->id);
        $this->assertEquals($this->valueLists[0]->name, $jsonValueList->name);
        $this->assertEquals($this->valueLists[0]->model_type, $jsonValueList->model_type);
        $this->assertEquals($this->valueLists[0]->values, $jsonValueList->values);
    }

    public function test_get_value_list_value_list_not_found()
    {
        $valueList = AdditionalColumn::factory()->create([
            'model_type' => User::class,
        ]);

        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/value-lists/' . $valueList->id + 1);
        $response->assertStatus(404);
    }

    public function test_get_value_lists()
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/value-lists');
        $response->assertStatus(200);

        $jsonValueLists = collect(json_decode($response->getContent())->data->data);
        foreach ($this->valueLists as $valueList) {
            $this->assertTrue($jsonValueLists->contains(function ($jsonValueList) use ($valueList) {
                return $jsonValueList->id === $valueList->id &&
                    $jsonValueList->name === $valueList->name &&
                    $jsonValueList->model_type === $valueList->model_type &&
                    $jsonValueList->values === $valueList->values &&
                    Carbon::parse($jsonValueList->created_at)->toDateTimeString() ===
                    Carbon::parse($valueList->created_at)->timezone('GMT')->toDateTimeString() &&
                    Carbon::parse($jsonValueList->updated_at)->toDateTimeString() ===
                    Carbon::parse($valueList->created_at)->timezone('GMT')->toDateTimeString();
            }));
        }
    }

    public function test_get_value_lists_by_model()
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $queryParams = '?filter[model_type]=user';
        $response = $this->actingAs($this->user)->get('/api/value-lists' . $queryParams);
        $response->assertStatus(200);

        $jsonValueLists = collect(json_decode($response->getContent())->data->data);

        $this->assertNotEmpty($jsonValueLists);
        $this->assertTrue($jsonValueLists->every(function ($value, $key) {
            return $value->model_type === User::class;
        }));

        $valueList = $this->valueLists[0];
        $this->assertTrue($jsonValueLists->contains(function ($jsonValueList) use ($valueList) {
            return $jsonValueList->id === $valueList->id &&
                $jsonValueList->name === $valueList->name &&
                $jsonValueList->model_type === $valueList->model_type &&
                $jsonValueList->values === $valueList->values &&
                Carbon::parse($jsonValueList->created_at)->toDateTimeString() ===
                Carbon::parse($valueList->created_at)->timezone('GMT')->toDateTimeString() &&
                Carbon::parse($jsonValueList->updated_at)->toDateTimeString() ===
                Carbon::parse($valueList->created_at)->timezone('GMT')->toDateTimeString();
        }));

        $valueList = $this->valueLists[2];
        $this->assertTrue($jsonValueLists->contains(function ($jsonValueList) use ($valueList) {
            return $jsonValueList->id === $valueList->id &&
                $jsonValueList->name === $valueList->name &&
                $jsonValueList->model_type === $valueList->model_type &&
                $jsonValueList->values === $valueList->values &&
                Carbon::parse($jsonValueList->created_at)->toDateTimeString() ===
                Carbon::parse($valueList->created_at)->timezone('GMT')->toDateTimeString() &&
                Carbon::parse($jsonValueList->updated_at)->toDateTimeString() ===
                Carbon::parse($valueList->created_at)->timezone('GMT')->toDateTimeString();
        }));
    }

    public function test_get_value_lists_model_not_found()
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $queryParams = '?filter[model_type]=zywx' . Str::random();
        $response = $this->actingAs($this->user)->get('/api/value-lists' . $queryParams);
        $response->assertStatus(200);
        $this->assertEmpty(json_decode($response->getContent())->data->data);
    }

    public function test_create_value_list()
    {
        $valueList = [
            'name' => 'hopefullyNeverExistingName' . Str::random(),
            'model_type' => 'user',
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

        $this->assertNotEmpty($dbValueList);
        $this->assertEquals($valueList['name'], $dbValueList->name);
        $this->assertEquals(Helper::classExists(classString: $valueList['model_type'], isModel: true), $dbValueList->model_type);
        $this->assertEquals($valueList['values'], $dbValueList->values);
    }

    public function test_create_value_list_validation_fails()
    {
        $valueList = [
            'name' => 'hopefullyNeverExistingName' . Str::random(),
            'model_type' => 'user',
            'values' => [],
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/value-lists', $valueList);
        $response->assertStatus(422);
    }

    public function test_create_value_list_values_no_list()
    {
        $valueList = [
            'name' => 'hopefullyNeverExistingName' . Str::random(),
            'model_type' => 'user',
            'values' => ['test' => 1, 3 => 'c', 'g'],
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/value-lists', $valueList);
        $response->assertStatus(422);
    }

    public function test_create_value_list_model_not_found()
    {
        $valueList = [
            'name' => 'hopefullyNeverExistingName' . Str::random(),
            'model_type' => 'user' . Str::random(),
            'values' => ['test', 1, 3, 'c', 'g'],
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/value-lists', $valueList);
        $response->assertStatus(404);
    }

    public function test_create_value_list_name_model_combination_already_exists()
    {
        $valueList = [
            'name' => $this->valueLists[0]->name,
            'model_type' => class_basename($this->valueLists[0]->model_type),
            'values' => ['test', 1, 3, 'c', 'g'],
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/value-lists', $valueList);
        $response->assertStatus(409);
    }

    public function test_update_value_list()
    {
        $valueList = [
            'id' => $this->valueLists[0]->id,
            'name' => 'hopefullyNeverExistingName' . Str::random(),
            'values' => ['test', 1, 3, 'c', 'g'],
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/value-lists', $valueList);
        $response->assertStatus(200);

        $jsonValueList = json_decode($response->getContent())->data;
        $dbValueList = $this->valueLists[0]->fresh();

        $this->assertEquals($valueList['id'], $jsonValueList->id);
        $this->assertEquals($valueList['id'], $dbValueList->id);
        $this->assertEquals($valueList['name'], $dbValueList->name);
        $this->assertEquals($valueList['values'], $dbValueList->values);
        $this->assertEquals($this->valueLists[0]->model_type, $dbValueList->model_type);
    }

    public function test_update_value_list_validation_fails()
    {
        $valueList = [
            'id' => $this->valueLists[0]->id,
            'name' => '',
            'values' => [],
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/value-lists', $valueList);
        $response->assertStatus(422);
    }

    public function test_update_value_list_values_no_list()
    {
        $valueList = [
            'id' => $this->valueLists[0]->id,
            'values' => ['test' => 1, 3 => 'c', 'g'],
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/value-lists', $valueList);
        $response->assertStatus(422);
    }

    public function test_update_value_list_name_model_combination_already_exists()
    {
        $valueList = [
            'id' => $this->valueLists[0]->id,
            'name' => $this->valueLists[2]->name,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/value-lists', $valueList);
        $response->assertStatus(409);
    }

    public function test_update_value_list_model_has_values_exists()
    {
        $valueList = [
            'id' => $this->valueLists[1]->id,
            'name' => 'hopefullyNeverExistingName' . Str::random(),
            'values' => ['test', 1, 3, 'c', 'g'],
        ];

        $category = Category::factory()->create(['model_type' => Model::class]);

        $category->saveMeta($this->valueLists[1]->name, array_pop($valueList['values']));

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/value-lists', $valueList);
        $response->assertStatus(409);
    }

    public function test_delete_value_list()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/value-lists/' . $this->valueLists[2]->id);
        $response->assertStatus(204);

        $this->assertEmpty($this->valueLists[2]->fresh());
    }

    public function test_delete_value_list_value_list_not_found()
    {
        $valueList = AdditionalColumn::factory()->create([
            'model_type' => User::class,
        ]);

        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/value-lists/' . $valueList->id);
        $response->assertStatus(404);
    }

    public function test_delete_value_list_model_has_values()
    {
        $category = Category::factory()->create(['model_type' => Model::class]);
        $category->saveMeta($this->valueLists[1]->name, $this->valueLists[1]->values[0]);

        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/value-lists/' . $this->valueLists[1]->id);
        $response->assertStatus(423);
    }
}
