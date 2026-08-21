<?php

use FluxErp\Models\Permission;
use FluxErp\Models\Warehouse;
use FluxErp\Models\WarehouseBin;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->warehouse = Warehouse::factory()->create();
    $this->warehouseBins = WarehouseBin::factory()
        ->count(3)
        ->create(['warehouse_id' => $this->warehouse->getKey()]);

    $this->permissions = [
        'show' => Permission::findOrCreate('api.warehouse-bins.{id}.get'),
        'index' => Permission::findOrCreate('api.warehouse-bins.get'),
        'create' => Permission::findOrCreate('api.warehouse-bins.post'),
        'update' => Permission::findOrCreate('api.warehouse-bins.put'),
        'delete' => Permission::findOrCreate('api.warehouse-bins.{id}.delete'),
    ];
});

test('get warehouse bin', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/warehouse-bins/' . $this->warehouseBins[0]->id);
    $response->assertOk();

    expect(json_decode($response->getContent())->data->code)->toEqual($this->warehouseBins[0]->code);
});

test('get warehouse bins', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/warehouse-bins');
    $response->assertOk();
});

test('create warehouse bin', function (): void {
    $warehouseBin = [
        'warehouse_id' => $this->warehouse->getKey(),
        'code' => Str::random(),
        'warehouse_bin_type_enum' => 'bin',
        'is_storage_location' => true,
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/warehouse-bins', $warehouseBin);
    $response->assertCreated();

    $responseBin = json_decode($response->getContent())->data;
    $dbBin = WarehouseBin::query()->whereKey($responseBin->id)->first();

    expect($dbBin->code)->toEqual($warehouseBin['code']);
});

test('create warehouse bin validation fails', function (): void {
    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/warehouse-bins', ['code' => Str::random()]);
    $response->assertUnprocessable();
});

test('update warehouse bin', function (): void {
    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/warehouse-bins', [
        'id' => $this->warehouseBins[0]->id,
        'name' => 'Umbenannt',
    ]);
    $response->assertOk();

    expect($this->warehouseBins[0]->fresh()->name)->toEqual('Umbenannt');
});

test('delete warehouse bin', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/warehouse-bins/' . $this->warehouseBins[0]->id);
    $response->assertNoContent();
});
