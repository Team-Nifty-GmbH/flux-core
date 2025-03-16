<?php

namespace FluxErp\Tests\Feature\Api;

use FluxErp\Enums\PropertyTypeEnum;
use FluxErp\Models\Client;
use FluxErp\Models\Permission;
use FluxErp\Models\Product;
use FluxErp\Models\ProductProperty;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

class ProductPropertyTest extends BaseSetup
{
    private array $permissions;

    private Collection $productProperties;

    private Model $products;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productProperties = ProductProperty::factory()->count(3)->create();
        $client = Client::factory()->create();

        $this->products = Product::factory()
            ->hasAttached(factory: $this->dbClient, relationship: 'clients')
            ->create();

        $this->products->productProperties()->sync($this->productProperties[1]->id);

        $this->user->clients()->attach($client->id);

        $this->permissions = [
            'show' => Permission::findOrCreate('api.product-properties.{id}.get'),
            'index' => Permission::findOrCreate('api.product-properties.get'),
            'create' => Permission::findOrCreate('api.product-properties.post'),
            'update' => Permission::findOrCreate('api.product-properties.put'),
            'delete' => Permission::findOrCreate('api.product-properties.{id}.delete'),
        ];
    }

    public function test_create_product_property(): void
    {
        $productProperty = [
            'name' => Str::random(),
            'property_type_enum' => PropertyTypeEnum::Text->value,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/product-properties', $productProperty);
        $response->assertStatus(201);

        $responseProductProperty = json_decode($response->getContent())->data;

        $dbProductProperty = ProductProperty::query()
            ->whereKey($responseProductProperty->id)
            ->first();

        $this->assertEquals($productProperty['name'], $dbProductProperty->name);
        $this->assertEquals($productProperty['property_type_enum'], $dbProductProperty->property_type_enum->value);
    }

    public function test_create_product_property_validation_fails(): void
    {
        $productProperty = [
            'name' => 123,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/product-properties', $productProperty);
        $response->assertStatus(422);
    }

    public function test_delete_product_property(): void
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)
            ->delete('/api/product-properties/' . $this->productProperties[0]->id);
        $response->assertStatus(204);

        $this->assertFalse(ProductProperty::query()->whereKey($this->productProperties[0]->id)->exists());
    }

    public function test_delete_product_property_product_property_has_product(): void
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)
            ->delete('/api/product-properties/' . $this->productProperties[1]->id);
        $response->assertStatus(423);
    }

    public function test_delete_product_property_product_property_not_found(): void
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)
            ->delete('/api/product-properties/' . ++$this->productProperties[2]->id);
        $response->assertStatus(404);
    }

    public function test_get_product_properties(): void
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/product-properties');
        $response->assertStatus(200);

        $productProperties = json_decode($response->getContent())->data->data;

        $this->assertEquals($this->productProperties[0]->id, $productProperties[0]->id);
        $this->assertEquals($this->productProperties[0]->name, $productProperties[0]->name);
    }

    public function test_get_product_property(): void
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/product-properties/' . $this->productProperties[0]->id);
        $response->assertStatus(200);

        $productProperty = json_decode($response->getContent())->data;

        $this->assertEquals($this->productProperties[0]->id, $productProperty->id);
        $this->assertEquals($this->productProperties[0]->name, $productProperty->name);
    }

    public function test_get_product_property_product_property_not_found(): void
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)
            ->get('/api/product-properties/' . $this->productProperties[2]->id + 10000);
        $response->assertStatus(404);
    }

    public function test_update_product_property(): void
    {
        $productProperty = [
            'id' => $this->productProperties[0]->id,
            'name' => Str::random(),
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/product-properties', $productProperty);
        $response->assertStatus(200);

        $responseProductProperty = json_decode($response->getContent())->data;

        $dbProductProperty = ProductProperty::query()
            ->whereKey($responseProductProperty->id)
            ->first();

        $this->assertEquals($productProperty['id'], $dbProductProperty->id);
        $this->assertEquals($productProperty['name'], $dbProductProperty->name);
    }

    public function test_update_product_property_validation_fails(): void
    {
        $productProperty = [
            'id' => $this->productProperties[0]->id,
            'name' => null,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/product-properties', $productProperty);
        $response->assertStatus(422);
    }
}
