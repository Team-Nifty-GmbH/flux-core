<?php

uses(FluxErp\Tests\Feature\BaseSetup::class);
use FluxErp\Models\Permission;
use FluxErp\Models\Product;
use FluxErp\Models\ProductOption;
use FluxErp\Models\ProductOptionGroup;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
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
});

test('create product option group', function (): void {
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

    expect($dbProductOptionGroup->name)->toEqual($productOptionGroup['name']);
});

test('create product option group validation fails', function (): void {
    $productOptionGroup = [
        'name' => 123,
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/product-option-groups', $productOptionGroup);
    $response->assertStatus(422);
});

test('delete product option group', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)
        ->delete('/api/product-option-groups/' . $this->productOptionGroups[0]->id);
    $response->assertStatus(204);

    expect(ProductOptionGroup::query()->whereKey($this->productOptionGroups[0]->id)->exists())->toBeFalse();
});

test('delete product option group group option has product', function (): void {
    $product = Product::factory()
        ->hasAttached(factory: $this->dbClient, relationship: 'clients')
        ->create();

    $product->productOptions()->attach($this->productOptions[1]->id);

    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)
        ->delete('/api/product-option-groups/' . $this->productOptionGroups[1]->id);
    $response->assertStatus(423);
});

test('delete product option group product option group not found', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)
        ->delete('/api/product-option-groups/' . ++$this->productOptionGroups[2]->id);
    $response->assertStatus(404);
});

test('get product option group', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/product-option-groups/'
        . $this->productOptionGroups[0]->id);
    $response->assertStatus(200);

    $productOptionGroup = json_decode($response->getContent())->data;

    expect($productOptionGroup->id)->toEqual($this->productOptionGroups[0]->id);
    expect($productOptionGroup->name)->toEqual($this->productOptionGroups[0]->name);
});

test('get product option group product option group not found', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/product-option-groups/'
        . $this->productOptionGroups[2]->id + 100);
    $response->assertStatus(404);
});

test('get product option groups', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/product-option-groups');
    $response->assertStatus(200);

    $productOptionGroups = json_decode($response->getContent())->data;

    expect($productOptionGroups->data[0]->id)->toEqual($this->productOptionGroups[0]->id);
    expect($productOptionGroups->data[0]->name)->toEqual($this->productOptionGroups[0]->name);
});

test('update product option group', function (): void {
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

    expect($dbProductOptionGroup->id)->toEqual($productOptionGroup['id']);
    expect($dbProductOptionGroup->name)->toEqual($productOptionGroup['name']);
});

test('update product option group validation fails', function (): void {
    $productOptionGroup = [
        'id' => $this->productOptionGroups[0]->id,
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/product-option-groups', $productOptionGroup);
    $response->assertStatus(422);
});
