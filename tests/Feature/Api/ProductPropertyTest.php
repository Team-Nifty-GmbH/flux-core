<?php

namespace FluxErp\Tests\Feature\Api;

use FluxErp\Models\Client;
use FluxErp\Models\Permission;
use FluxErp\Models\Product;
use FluxErp\Models\ProductProperty;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;

class ProductPropertyTest extends BaseSetup
{
    use DatabaseTransactions;

    private Model $products;

    private Collection $productProperties;

    private array $permissions;

    public function setUp(): void
    {
        parent::setUp();

        $this->productProperties = ProductProperty::factory()->count(3)->create();
        $client = Client::factory()->create();

        $this->products = Product::factory()->create([
            'client_id' => $client->id,
        ]);

        $this->products->productProperties()->sync($this->productProperties[1]->id);

        $this->permissions = [
            'show' => Permission::findOrCreate('api.product-properties.{id}.get'),
            'index' => Permission::findOrCreate('api.product-properties.get'),
            'create' => Permission::findOrCreate('api.product-properties.post'),
            'update' => Permission::findOrCreate('api.product-properties.put'),
            'delete' => Permission::findOrCreate('api.product-properties.{id}.delete'),
        ];

        $this->app->make(PermissionRegistrar::class)->registerPermissions();
    }

    public function test_get_product_property()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/product-properties/' . $this->productProperties[0]->id);
        $response->assertStatus(200);

        $productProperty = json_decode($response->getContent())->data;

        $this->assertEquals($this->productProperties[0]->id, $productProperty->id);
        $this->assertEquals($this->productProperties[0]->name, $productProperty->name);
    }

    public function test_get_product_property_product_property_not_found()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/product-properties/' . Str::uuid());
        $response->assertStatus(404);
    }

    public function test_get_product_properties()
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/product-properties');
        $response->assertStatus(200);

        $productProperties = json_decode($response->getContent())->data->data;

        $this->assertEquals($this->productProperties[0]->id, $productProperties[0]->id);
        $this->assertEquals($this->productProperties[0]->name, $productProperties[0]->name);
    }

    public function test_create_product_property()
    {
        $productProperty = [
            'name' => Str::random(),
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
    }

    public function test_create_product_property_validation_fails()
    {
        $productProperty = [
            'name' => 123,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/product-properties', $productProperty);
        $response->assertStatus(422);
    }

    public function test_update_product_property()
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

    public function test_update_product_property_validation_fails()
    {
        $productProperty = [
            'id' => $this->productProperties[0]->id,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/product-properties', $productProperty);
        $response->assertStatus(422);
    }

    public function test_delete_product_property()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)
            ->delete('/api/product-properties/' . $this->productProperties[0]->id);
        $response->assertStatus(204);

        $this->assertFalse(ProductProperty::query()->whereKey($this->productProperties[0]->id)->exists());
    }

    public function test_delete_product_property_product_property_not_found()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)
            ->delete('/api/product-properties/' . ++$this->productProperties[2]->id);
        $response->assertStatus(404);
    }

    public function test_delete_product_property_product_property_has_product()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)
            ->delete('/api/product-properties/' . $this->productProperties[1]->id);
        $response->assertStatus(423);
    }
}
