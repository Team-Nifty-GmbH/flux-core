<?php

use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Permission;
use FluxErp\Models\Warehouse;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $dbContact = Contact::factory()->create([
        'client_id' => $this->dbClient->getKey(),
    ]);

    $this->addresses = Address::factory()->count(3)->create([
        'contact_id' => $dbContact->id,
        'client_id' => $this->dbClient->getKey(),
        'is_main_address' => false,
    ]);

    $this->warehouses = Warehouse::factory()->count(3)->create([
        'address_id' => $this->addresses[0]->id,
    ]);

    $this->permissions = [
        'show' => Permission::findOrCreate('api.warehouses.{id}.get'),
        'index' => Permission::findOrCreate('api.warehouses.get'),
        'create' => Permission::findOrCreate('api.warehouses.post'),
        'update' => Permission::findOrCreate('api.warehouses.put'),
        'delete' => Permission::findOrCreate('api.warehouses.{id}.delete'),
    ];
});

test('create warehouse', function (): void {
    $warehouse = [
        'name' => Str::random(),
        'address_id' => $this->addresses[0]->id,
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/warehouses', $warehouse);
    $response->assertCreated();

    $responseWarehouse = json_decode($response->getContent())->data;
    $dbWarehouse = Warehouse::query()
        ->whereKey($responseWarehouse->id)
        ->first();

    expect($dbWarehouse->name)->toEqual($warehouse['name']);
    expect($dbWarehouse->address_id)->toEqual($warehouse['address_id']);
});

test('create warehouse validation fails', function (): void {
    $warehouse = [
        'address_id' => $this->addresses[0]->id,
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/warehouses', $warehouse);
    $response->assertUnprocessable();
});

test('delete warehouse', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/warehouses/' . $this->warehouses[0]->id);
    $response->assertNoContent();

    expect(Warehouse::query()->whereKey($this->warehouses[0]->id)->exists())->toBeFalse();
});

test('delete warehouse warehouse not found', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/warehouses/' . ++$this->warehouses[2]->id);
    $response->assertNotFound();
});

test('get warehouse', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/warehouses/' . $this->warehouses[0]->id);
    $response->assertOk();

    $warehouse = json_decode($response->getContent())->data;

    expect($warehouse->id)->toEqual($this->warehouses[0]->id);
    expect($warehouse->address_id)->toEqual($this->warehouses[0]->address_id);
    expect($warehouse->name)->toEqual($this->warehouses[0]->name);
});

test('get warehouse warehouse not found', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/warehouses/' . $this->warehouses[2]->id + 10000);
    $response->assertNotFound();
});

test('get warehouses', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/warehouses');
    $response->assertOk();

    $warehouses = json_decode($response->getContent())->data;

    expect($warehouses->data[0]->id)->toEqual($this->warehouses[0]->id);
    expect($warehouses->data[0]->address_id)->toEqual($this->warehouses[0]->address_id);
    expect($warehouses->data[0]->name)->toEqual($this->warehouses[0]->name);
    expect($warehouses->data[1]->id)->toEqual($this->warehouses[1]->id);
    expect($warehouses->data[1]->address_id)->toEqual($this->warehouses[1]->address_id);
    expect($warehouses->data[1]->name)->toEqual($this->warehouses[1]->name);
    expect($warehouses->data[2]->id)->toEqual($this->warehouses[2]->id);
    expect($warehouses->data[2]->address_id)->toEqual($this->warehouses[2]->address_id);
    expect($warehouses->data[2]->name)->toEqual($this->warehouses[2]->name);
});

test('update warehouse', function (): void {
    $warehouse = [
        'id' => $this->warehouses[0]->id,
        'name' => Str::random(),
        'address_id' => $this->addresses[0]->id,
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/warehouses', $warehouse);
    $response->assertOk();

    $responseWarehouse = json_decode($response->getContent())->data;
    $dbWarehouse = Warehouse::query()
        ->whereKey($responseWarehouse->id)
        ->first();

    expect($dbWarehouse->id)->toEqual($warehouse['id']);
    expect($dbWarehouse->name)->toEqual($warehouse['name']);
    expect($dbWarehouse->address_id)->toEqual($warehouse['address_id']);
});

test('update warehouse validation fails', function (): void {
    $warehouse = [
        'name' => Str::random(),
        'address_id' => $this->addresses[0]->id,
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/warehouses', $warehouse);
    $response->assertUnprocessable();
});
