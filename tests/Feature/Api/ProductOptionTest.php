<?php

namespace FluxErp\Tests\Feature\Api;

use FluxErp\Models\Permission;
use FluxErp\Models\ProductOption;
use FluxErp\Models\ProductOptionGroup;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

class ProductOptionTest extends BaseSetup
{
    private array $permissions;

    private Collection $productOptionGroups;

    private Collection $productOptions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productOptionGroups = ProductOptionGroup::factory()->count(3)->create();

        $this->productOptions = ProductOption::factory()->count(3)->create([
            'product_option_group_id' => $this->productOptionGroups[0]->id,
        ]);

        $this->permissions = [
            'show' => Permission::findOrCreate('api.product-options.{id}.get'),
            'index' => Permission::findOrCreate('api.product-options.get'),
            'create' => Permission::findOrCreate('api.product-options.post'),
            'update' => Permission::findOrCreate('api.product-options.put'),
            'delete' => Permission::findOrCreate('api.product-options.{id}.delete'),
        ];
    }

    public function test_create_product_option(): void
    {
        $productOption = [
            'product_option_group_id' => $this->productOptionGroups[0]->id,
            'name' => Str::random(),
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/product-options', $productOption);
        $response->assertStatus(201);

        $responseProductOption = json_decode($response->getContent())->data;

        $dbProductOption = ProductOption::query()
            ->whereKey($responseProductOption->id)
            ->first();

        $this->assertEquals($productOption['product_option_group_id'], $dbProductOption->product_option_group_id);
        $this->assertEquals($productOption['name'], $dbProductOption->name);
    }

    public function test_create_product_option_validation_fails(): void
    {
        $productOption = [
            'product_option_group_id' => $this->productOptionGroups[0]->id,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/product-options', $productOption);
        $response->assertStatus(422);
    }

    public function test_delete_product_option(): void
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/product-options/' . $this->productOptions[0]->id);
        $response->assertStatus(204);

        $this->assertFalse(ProductOption::query()->whereKey($this->productOptions[0]->id)->exists());
    }

    public function test_delete_product_option_product_option_not_found(): void
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/product-options/' . ++$this->productOptions[2]->id);
        $response->assertStatus(404);
    }

    public function test_get_product_option(): void
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/product-options/' . $this->productOptions[0]->id);
        $response->assertStatus(200);

        $productOption = json_decode($response->getContent())->data;

        $this->assertEquals($this->productOptions[0]->id, $productOption->id);
        $this->assertEquals($this->productOptions[0]->name, $productOption->name);
        $this->assertEquals($this->productOptions[0]->product_option_group_id, $productOption->product_option_group_id);
    }

    public function test_get_product_option_product_option_not_found(): void
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)
            ->get('/api/product-options/' . $this->productOptions[2]->id + 10000);
        $response->assertStatus(404);
    }

    public function test_get_product_options(): void
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/product-options');
        $response->assertStatus(200);

        $productOptions = json_decode($response->getContent())->data;

        $this->assertEquals($this->productOptions[0]->id, $productOptions->data[0]->id);
        $this->assertEquals($this->productOptions[0]->name, $productOptions->data[0]->name);
        $this->assertEquals($this->productOptions[0]->product_option_group_id,
            $productOptions->data[0]->product_option_group_id);
    }

    public function test_update_product_option(): void
    {
        $productOption = [
            'id' => $this->productOptions[0]->id,
            'product_option_group_id' => $this->productOptionGroups[0]->id,
            'name' => Str::random(),
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/product-options', $productOption);
        $response->assertStatus(200);

        $responseProductOption = json_decode($response->getContent())->data;

        $dbProductOption = ProductOption::query()
            ->whereKey($responseProductOption->id)
            ->first();

        $this->assertEquals($productOption['id'], $dbProductOption->id);
        $this->assertEquals($productOption['product_option_group_id'], $dbProductOption->product_option_group_id);
        $this->assertEquals($productOption['name'], $dbProductOption->name);
    }

    public function test_update_product_option_validation_fails(): void
    {
        $productOption = [
            'product_option_group_id' => $this->productOptionGroups[0]->id,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/product-options', $productOption);
        $response->assertStatus(422);
    }
}
