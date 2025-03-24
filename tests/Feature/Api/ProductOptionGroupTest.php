<?php

namespace FluxErp\Tests\Feature\Api;

use FluxErp\Models\Permission;
use FluxErp\Models\Product;
use FluxErp\Models\ProductOption;
use FluxErp\Models\ProductOptionGroup;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

class ProductOptionGroupTest extends BaseSetup
{
    private array $permissions;

    private Collection $productOptionGroups;

    private Collection $productOptions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productOptionGroups = ProductOptionGroup::factory()->count(3)->create();

        $this->productOptions = ProductOption::factory()->count(3)->create([
            'product_option_group_id' => $this->productOptionGroups[1]->id,
        ]);

        $this->permissions = [
            'show' => Permission::findOrCreate('api.product-option-groups.{id}.get'),
            'index' => Permission::findOrCreate('api.product-option-groups.get'),
            'create' => Permission::findOrCreate('api.product-option-groups.post'),
            'update' => Permission::findOrCreate('api.product-option-groups.put'),
            'delete' => Permission::findOrCreate('api.product-option-groups.{id}.delete'),
        ];
    }

    public function test_create_product_option_group(): void
    {
        $productOptionGroup = [
            'name' => Str::random(),
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/product-option-groups', $productOptionGroup);
        $response->assertStatus(201);

        $responseProductOptionGroup = json_decode($response->getContent())->data;

        $dbProductOptionGroup = ProductOptionGroup::query()
            ->whereKey($responseProductOptionGroup->id)
            ->first();

        $this->assertEquals($productOptionGroup['name'], $dbProductOptionGroup->name);
    }

    public function test_create_product_option_group_validation_fails(): void
    {
        $productOptionGroup = [
            'name' => 123,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/product-option-groups', $productOptionGroup);
        $response->assertStatus(422);
    }

    public function test_delete_product_option_group(): void
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)
            ->delete('/api/product-option-groups/' . $this->productOptionGroups[0]->id);
        $response->assertStatus(204);

        $this->assertFalse(ProductOptionGroup::query()->whereKey($this->productOptionGroups[0]->id)->exists());
    }

    public function test_delete_product_option_group_group_option_has_product(): void
    {
        $product = Product::factory()
            ->hasAttached(factory: $this->dbClient, relationship: 'clients')
            ->create();

        $product->productOptions()->attach($this->productOptions[1]->id);

        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)
            ->delete('/api/product-option-groups/' . $this->productOptionGroups[1]->id);
        $response->assertStatus(423);
    }

    public function test_delete_product_option_group_product_option_group_not_found(): void
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)
            ->delete('/api/product-option-groups/' . ++$this->productOptionGroups[2]->id);
        $response->assertStatus(404);
    }

    public function test_get_product_option_group(): void
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/product-option-groups/'
            . $this->productOptionGroups[0]->id);
        $response->assertStatus(200);

        $productOptionGroup = json_decode($response->getContent())->data;

        $this->assertEquals($this->productOptionGroups[0]->id, $productOptionGroup->id);
        $this->assertEquals($this->productOptionGroups[0]->name, $productOptionGroup->name);
    }

    public function test_get_product_option_group_product_option_group_not_found(): void
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/product-option-groups/'
            . $this->productOptionGroups[2]->id + 100);
        $response->assertStatus(404);
    }

    public function test_get_product_option_groups(): void
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/product-option-groups');
        $response->assertStatus(200);

        $productOptionGroups = json_decode($response->getContent())->data;

        $this->assertEquals($this->productOptionGroups[0]->id, $productOptionGroups->data[0]->id);
        $this->assertEquals($this->productOptionGroups[0]->name, $productOptionGroups->data[0]->name);
    }

    public function test_update_product_option_group(): void
    {
        $productOptionGroup = [
            'id' => $this->productOptionGroups[0]->id,
            'name' => Str::random(),
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/product-option-groups', $productOptionGroup);
        $response->assertStatus(200);

        $responseProductOptionGroup = json_decode($response->getContent())->data;

        $dbProductOptionGroup = ProductOptionGroup::query()
            ->whereKey($responseProductOptionGroup->id)
            ->first();

        $this->assertEquals($productOptionGroup['id'], $dbProductOptionGroup->id);
        $this->assertEquals($productOptionGroup['name'], $dbProductOptionGroup->name);
    }

    public function test_update_product_option_group_validation_fails(): void
    {
        $productOptionGroup = [
            'id' => $this->productOptionGroups[0]->id,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/product-option-groups', $productOptionGroup);
        $response->assertStatus(422);
    }
}
