<?php

use FluxErp\Enums\PropertyTypeEnum;
use FluxErp\Models\Client;
use FluxErp\Models\Permission;
use FluxErp\Models\Product;
use FluxErp\Models\ProductProperty;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
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
});

test('create product property', function (): void {
    $productProperty = [
        'name' => Str::random(),
        'property_type_enum' => PropertyTypeEnum::Text->value,
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/product-properties', $productProperty);
    $response->assertCreated();

    $responseProductProperty = json_decode($response->getContent())->data;

    $dbProductProperty = ProductProperty::query()
        ->whereKey($responseProductProperty->id)
        ->first();

    expect($dbProductProperty->name)->toEqual($productProperty['name']);
    expect($dbProductProperty->property_type_enum->value)->toEqual($productProperty['property_type_enum']);
});

test('create product property validation fails', function (): void {
    $productProperty = [
        'name' => 123,
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/product-properties', $productProperty);
    $response->assertUnprocessable();
});

test('delete product property', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)
        ->delete('/api/product-properties/' . $this->productProperties[0]->id);
    $response->assertNoContent();

    expect(ProductProperty::query()->whereKey($this->productProperties[0]->id)->exists())->toBeFalse();
});

test('delete product property product property has product', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)
        ->delete('/api/product-properties/' . $this->productProperties[1]->id);
    $response->assertStatus(423);
});

test('delete product property product property not found', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)
        ->delete('/api/product-properties/' . ++$this->productProperties[2]->id);
    $response->assertNotFound();
});

test('get product properties', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/product-properties');
    $response->assertOk();

    $productProperties = json_decode($response->getContent())->data->data;

    expect($productProperties[0]->id)->toEqual($this->productProperties[0]->id);
    expect($productProperties[0]->name)->toEqual($this->productProperties[0]->name);
});

test('get product property', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/product-properties/' . $this->productProperties[0]->id);
    $response->assertOk();

    $productProperty = json_decode($response->getContent())->data;

    expect($productProperty->id)->toEqual($this->productProperties[0]->id);
    expect($productProperty->name)->toEqual($this->productProperties[0]->name);
});

test('get product property product property not found', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)
        ->get('/api/product-properties/' . $this->productProperties[2]->id + 10000);
    $response->assertNotFound();
});

test('update product property', function (): void {
    $productProperty = [
        'id' => $this->productProperties[0]->id,
        'name' => Str::random(),
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/product-properties', $productProperty);
    $response->assertOk();

    $responseProductProperty = json_decode($response->getContent())->data;

    $dbProductProperty = ProductProperty::query()
        ->whereKey($responseProductProperty->id)
        ->first();

    expect($dbProductProperty->id)->toEqual($productProperty['id']);
    expect($dbProductProperty->name)->toEqual($productProperty['name']);
});

test('update product property validation fails', function (): void {
    $productProperty = [
        'id' => $this->productProperties[0]->id,
        'name' => null,
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/product-properties', $productProperty);
    $response->assertUnprocessable();
});
