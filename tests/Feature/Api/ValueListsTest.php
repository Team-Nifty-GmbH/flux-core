<?php

namespace FluxErp\Tests\Feature\Api;

use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\Category;
use FluxErp\Models\Permission;
use FluxErp\Models\User;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

class ValueListsTest extends BaseSetup
{
    private array $permissions;

    private array $valueLists;

    protected function setUp(): void
    {
        parent::setUp();
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
    }

    public function test_create_value_list(): void
    {
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

        $this->assertNotEmpty($dbValueList);
        $this->assertEquals($valueList['name'], $dbValueList->name);
        $this->assertEquals($valueList['model_type'], $dbValueList->model_type);
        $this->assertEquals($valueList['values'], $dbValueList->values);
    }

    public function test_delete_value_list_model_has_values(): void
    {
        $category = Category::factory()->create(['model_type' => Model::class]);
        $category->saveMeta($this->valueLists[1]->name, $this->valueLists[1]->values[0]);

        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/value-lists/' . $this->valueLists[1]->id);
        $response->assertStatus(423);
    }

    public function test_delete_value_list_value_list_not_found(): void
    {
        $valueList = AdditionalColumn::factory()->create([
            'model_type' => morph_alias(User::class),
        ]);

        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/value-lists/' . $valueList->id);
        $response->assertStatus(422);
    }

    public function test_update_value_list_name_model_combination_already_exists(): void
    {
        $valueList = [
            'id' => $this->valueLists[0]->id,
            'name' => $this->valueLists[2]->name,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/value-lists', $valueList);
        $response->assertStatus(422);
    }
}
