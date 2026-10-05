<?php

use FluxErp\Models\Permission;
use FluxErp\Models\StorageArea;
use FluxErp\Models\Warehouse;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->warehouse = Warehouse::factory()->create();
    $this->storageAreas = StorageArea::factory()
        ->count(3)
        ->create(['warehouse_id' => $this->warehouse->getKey()]);

    $this->permissions = [
        'show' => Permission::findOrCreate('api.storage-areas.{id}.get'),
        'index' => Permission::findOrCreate('api.storage-areas.get'),
        'create' => Permission::findOrCreate('api.storage-areas.post'),
        'update' => Permission::findOrCreate('api.storage-areas.put'),
        'delete' => Permission::findOrCreate('api.storage-areas.{id}.delete'),
    ];
});

test('get storage area', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/storage-areas/' . $this->storageAreas[0]->id);
    $response->assertOk();

    expect(json_decode($response->getContent())->data->code)->toEqual($this->storageAreas[0]->code);
});

test('get storage areas', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/storage-areas');
    $response->assertOk();
});

test('create storage area', function (): void {
    $storageArea = [
        'warehouse_id' => $this->warehouse->getKey(),
        'code' => Str::random(),
        'storage_area_type_enum' => 'container',
        'is_storage_location' => true,
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/storage-areas', $storageArea);
    $response->assertCreated();

    $responseBin = json_decode($response->getContent())->data;
    $dbBin = StorageArea::query()->whereKey($responseBin->id)->first();

    expect($dbBin->code)->toEqual($storageArea['code']);
});

test('create storage area validation fails', function (): void {
    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/storage-areas', ['code' => Str::random()]);
    $response->assertUnprocessable();
});

test('update storage area', function (): void {
    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/storage-areas', [
        'id' => $this->storageAreas[0]->id,
        'name' => 'Umbenannt',
    ]);
    $response->assertOk();

    expect($this->storageAreas[0]->fresh()->name)->toEqual('Umbenannt');
});

test('delete storage area', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/storage-areas/' . $this->storageAreas[0]->id);
    $response->assertNoContent();
});
