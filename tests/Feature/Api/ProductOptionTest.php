<?php

use FluxErp\Models\Permission;
use FluxErp\Models\ProductOption;
use FluxErp\Models\ProductOptionGroup;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
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
});

test('create product option', function (): void {
    $productOption = [
        'product_option_group_id' => $this->productOptionGroups[0]->id,
        'name' => Str::random(),
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/product-options', $productOption);
    $response->assertCreated();

    $responseProductOption = json_decode($response->getContent())->data;

    $dbProductOption = ProductOption::query()
        ->whereKey($responseProductOption->id)
        ->first();

    expect($dbProductOption->product_option_group_id)->toEqual($productOption['product_option_group_id']);
    expect($dbProductOption->name)->toEqual($productOption['name']);
});

test('create product option validation fails', function (): void {
    $productOption = [
        'product_option_group_id' => $this->productOptionGroups[0]->id,
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/product-options', $productOption);
    $response->assertUnprocessable();
});

test('delete product option', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/product-options/' . $this->productOptions[0]->id);
    $response->assertNoContent();

    expect(ProductOption::query()->whereKey($this->productOptions[0]->id)->exists())->toBeFalse();
});

test('delete product option product option not found', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/product-options/' . ++$this->productOptions[2]->id);
    $response->assertNotFound();
});

test('get product option', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/product-options/' . $this->productOptions[0]->id);
    $response->assertOk();

    $productOption = json_decode($response->getContent())->data;

    expect($productOption->id)->toEqual($this->productOptions[0]->id);
    expect($productOption->name)->toEqual($this->productOptions[0]->name);
    expect($productOption->product_option_group_id)->toEqual($this->productOptions[0]->product_option_group_id);
});

test('get product option product option not found', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)
        ->get('/api/product-options/' . $this->productOptions[2]->id + 10000);
    $response->assertNotFound();
});

test('get product options', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/product-options');
    $response->assertOk();

    $productOptions = json_decode($response->getContent())->data;

    expect($productOptions->data[0]->id)->toEqual($this->productOptions[0]->id);
    expect($productOptions->data[0]->name)->toEqual($this->productOptions[0]->name);
    expect($productOptions->data[0]->product_option_group_id)->toEqual($this->productOptions[0]->product_option_group_id);
});

test('update product option', function (): void {
    $productOption = [
        'id' => $this->productOptions[0]->id,
        'product_option_group_id' => $this->productOptionGroups[0]->id,
        'name' => Str::random(),
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/product-options', $productOption);
    $response->assertOk();

    $responseProductOption = json_decode($response->getContent())->data;

    $dbProductOption = ProductOption::query()
        ->whereKey($responseProductOption->id)
        ->first();

    expect($dbProductOption->id)->toEqual($productOption['id']);
    expect($dbProductOption->product_option_group_id)->toEqual($productOption['product_option_group_id']);
    expect($dbProductOption->name)->toEqual($productOption['name']);
});

test('update product option validation fails', function (): void {
    $productOption = [
        'product_option_group_id' => $this->productOptionGroups[0]->id,
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/product-options', $productOption);
    $response->assertUnprocessable();
});
