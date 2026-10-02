<?php

use FluxErp\Models\Permission;
use FluxErp\Models\Resource;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->resource = Resource::factory()->create();

    $this->permissions = [
        'show' => Permission::findOrCreate('api.resources.{id}.get'),
        'index' => Permission::findOrCreate('api.resources.get'),
        'create' => Permission::findOrCreate('api.resources.post'),
        'update' => Permission::findOrCreate('api.resources.put'),
        'delete' => Permission::findOrCreate('api.resources.{id}.delete'),
    ];
});

test('create resource', function (): void {
    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/resources', [
        'name' => 'Test Room A',
    ]);

    $response->assertCreated();

    $responseResource = json_decode($response->getContent())->data;

    $dbResource = Resource::query()
        ->whereKey($responseResource->id)
        ->first();

    expect($dbResource->name)->toEqual('Test Room A');
});

test('create resource validation fails', function (): void {
    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/resources', []);

    $response->assertUnprocessable();
});

test('get resource', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/resources/' . $this->resource->getKey());
    $response->assertOk();

    $resource = json_decode($response->getContent())->data;

    expect($resource->id)->toEqual($this->resource->getKey());
    expect($resource->name)->toEqual($this->resource->name);
});

test('get resource not found', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/resources/' . ($this->resource->getKey() + 10000));
    $response->assertNotFound();
});

test('get resources', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/resources');
    $response->assertOk();

    $resources = json_decode($response->getContent())->data->data;

    expect($resources[0]->id)->toEqual($this->resource->getKey());
    expect($resources[0]->name)->toEqual($this->resource->name);
});

test('update resource', function (): void {
    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/resources', [
        'id' => $this->resource->getKey(),
        'name' => 'Updated Room',
    ]);

    $response->assertOk();

    $dbResource = Resource::query()
        ->whereKey($this->resource->getKey())
        ->first();

    expect($dbResource->name)->toEqual('Updated Room');
});

test('update resource validation fails', function (): void {
    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/resources', [
        'id' => $this->resource->getKey(),
        'name' => null,
    ]);

    $response->assertUnprocessable();
});

test('delete resource', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/resources/' . $this->resource->getKey());
    $response->assertNoContent();

    expect(Resource::query()->whereKey($this->resource->getKey())->exists())->toBeFalse();
    expect(Resource::withTrashed()->whereKey($this->resource->getKey())->exists())->toBeTrue();
});

test('delete resource not found', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/resources/' . ($this->resource->getKey() + 10000));
    $response->assertNotFound();
});
