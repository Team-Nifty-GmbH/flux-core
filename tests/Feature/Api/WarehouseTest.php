<?php

namespace FluxErp\Tests\Feature\Api;

use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Permission;
use FluxErp\Models\Warehouse;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

class WarehouseTest extends BaseSetup
{
    private Collection $addresses;

    private array $permissions;

    private Collection $warehouses;

    protected function setUp(): void
    {
        parent::setUp();

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
    }

    public function test_create_warehouse(): void
    {
        $warehouse = [
            'name' => Str::random(),
            'address_id' => $this->addresses[0]->id,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/warehouses', $warehouse);
        $response->assertStatus(201);

        $responseWarehouse = json_decode($response->getContent())->data;
        $dbWarehouse = Warehouse::query()
            ->whereKey($responseWarehouse->id)
            ->first();

        $this->assertEquals($warehouse['name'], $dbWarehouse->name);
        $this->assertEquals($warehouse['address_id'], $dbWarehouse->address_id);
    }

    public function test_create_warehouse_validation_fails(): void
    {
        $warehouse = [
            'address_id' => $this->addresses[0]->id,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/warehouses', $warehouse);
        $response->assertStatus(422);
    }

    public function test_delete_warehouse(): void
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/warehouses/' . $this->warehouses[0]->id);
        $response->assertStatus(204);

        $this->assertFalse(Warehouse::query()->whereKey($this->warehouses[0]->id)->exists());
    }

    public function test_delete_warehouse_warehouse_not_found(): void
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/warehouses/' . ++$this->warehouses[2]->id);
        $response->assertStatus(404);
    }

    public function test_get_warehouse(): void
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/warehouses/' . $this->warehouses[0]->id);
        $response->assertStatus(200);

        $warehouse = json_decode($response->getContent())->data;

        $this->assertEquals($this->warehouses[0]->id, $warehouse->id);
        $this->assertEquals($this->warehouses[0]->address_id, $warehouse->address_id);
        $this->assertEquals($this->warehouses[0]->name, $warehouse->name);
    }

    public function test_get_warehouse_warehouse_not_found(): void
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/warehouses/' . $this->warehouses[2]->id + 10000);
        $response->assertStatus(404);
    }

    public function test_get_warehouses(): void
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/warehouses');
        $response->assertStatus(200);

        $warehouses = json_decode($response->getContent())->data;

        $this->assertEquals($this->warehouses[0]->id, $warehouses->data[0]->id);
        $this->assertEquals($this->warehouses[0]->address_id, $warehouses->data[0]->address_id);
        $this->assertEquals($this->warehouses[0]->name, $warehouses->data[0]->name);
        $this->assertEquals($this->warehouses[1]->id, $warehouses->data[1]->id);
        $this->assertEquals($this->warehouses[1]->address_id, $warehouses->data[1]->address_id);
        $this->assertEquals($this->warehouses[1]->name, $warehouses->data[1]->name);
        $this->assertEquals($this->warehouses[2]->id, $warehouses->data[2]->id);
        $this->assertEquals($this->warehouses[2]->address_id, $warehouses->data[2]->address_id);
        $this->assertEquals($this->warehouses[2]->name, $warehouses->data[2]->name);
    }

    public function test_update_warehouse(): void
    {
        $warehouse = [
            'id' => $this->warehouses[0]->id,
            'name' => Str::random(),
            'address_id' => $this->addresses[0]->id,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/warehouses', $warehouse);
        $response->assertStatus(200);

        $responseWarehouse = json_decode($response->getContent())->data;
        $dbWarehouse = Warehouse::query()
            ->whereKey($responseWarehouse->id)
            ->first();

        $this->assertEquals($warehouse['id'], $dbWarehouse->id);
        $this->assertEquals($warehouse['name'], $dbWarehouse->name);
        $this->assertEquals($warehouse['address_id'], $dbWarehouse->address_id);
    }

    public function test_update_warehouse_validation_fails(): void
    {
        $warehouse = [
            'name' => Str::random(),
            'address_id' => $this->addresses[0]->id,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/warehouses', $warehouse);
        $response->assertStatus(422);
    }
}
