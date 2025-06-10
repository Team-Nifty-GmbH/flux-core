<?php

namespace FluxErp\Tests\Feature\Api;

use FluxErp\Models\Address;
use FluxErp\Models\AddressType;
use FluxErp\Models\Contact;
use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Laravel\Sanctum\Sanctum;

class AddressTypeTest extends BaseSetup
{
    private array $permissions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->permissions = [
            'show' => Permission::findOrCreate('api.address-types.{id}.get'),
            'index' => Permission::findOrCreate('api.address-types.get'),
            'create' => Permission::findOrCreate('api.address_types.post'),
            'update' => Permission::findOrCreate('api.address_types.put'),
            'delete' => Permission::findOrCreate('api.address_types.{id}.delete'),
        ];
    }

    public function test_create_address_type(): void
    {
        $payload = [
            'name' => 'Office',
            'client_id' => $this->dbClient->getKey(),
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/address-types', $payload);
        $response->assertStatus(201);

        $data = json_decode($response->getContent(), true)['data'];

        $this->assertArrayHasKey('id', $data);
        $this->assertEquals($payload['name'], $data['name']);
        $this->assertDatabaseHas('address_types', [
            'id' => $data['id'],
            'name' => 'Office',
            'client_id' => $this->dbClient->getKey(),
        ]);
    }

    public function test_create_address_type_validation_fails(): void
    {
        $payload = [];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/address-types', $payload);
        $response->assertStatus(422);

        $this->assertDatabaseMissing('address_types', []);
    }

    public function test_delete_address_type(): void
    {
        $type = AddressType::factory()->create([
            'name' => 'Home',
            'client_id' => $this->dbClient->getKey(),
            'is_locked' => false,
        ]);

        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/address-types/' . $type->getKey());
        $response->assertStatus(204);

        $this->assertSoftDeleted('address_types', ['id' => $type->getKey()]);
    }

    public function test_delete_address_type_locked(): void
    {
        $type = AddressType::factory()->create([
            'name' => 'Home',
            'client_id' => $this->dbClient->getKey(),
            'is_locked' => true,
        ]);

        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/address-types/' . $type->getKey());
        $response->assertStatus(422);

        $this->assertDatabaseHas('address_types', [
            'id' => $type->getKey(),
            'name' => 'Home',
            'client_id' => $this->dbClient->getKey(),
            'is_locked' => true,
        ]);
    }

    public function test_delete_address_type_with_attached_addresses(): void
    {
        $contact = Contact::factory()->create([
            'client_id' => $this->dbClient->getKey(),
        ]);

        $type = AddressType::factory()->create([
            'name' => 'TestType',
            'client_id' => $this->dbClient->getKey(),
        ]);

        Address::factory()
            ->hasAttached(
                $type
            )
            ->create([
                'contact_id' => $contact->getKey(),
                'client_id' => $this->dbClient->getKey(),
            ]);

        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/address-types/' . $type->getKey());
        $response->assertStatus(422);

        $this->assertDatabaseHas('address_types', [
            'id' => $type->getKey(),
            'name' => 'TestType',
            'client_id' => $this->dbClient->getKey(),
        ]);
    }

    public function test_get_address_types(): void
    {
        $addressTypes = AddressType::factory()->count(3)->create([
            'client_id' => $this->dbClient->getKey(),
        ]);

        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/address-types');
        $response->assertStatus(200);

        $jsonTypes = collect(json_decode($response->getContent())->data->data);

        $this->assertGreaterThanOrEqual(2, count($jsonTypes));

        foreach ($addressTypes as $addressType) {
            $this->assertTrue($jsonTypes->contains(fn ($item) => $item->id === $addressType->getKey() &&
                $item->name === $addressType->name
            ));
        }
    }

    public function test_get_non_existent_address_types(): void
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/address-types/' . '1');
        $response->assertStatus(404);
    }

    public function test_get_specific_address_types(): void
    {
        $addressTypes = AddressType::factory()->count(2)->create([
            'client_id' => $this->dbClient->getKey(),
        ]);

        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/address-types/' . $addressTypes[0]->getKey());
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $jsonType = $json->data;

        $this->assertNotEmpty($jsonType);
        $this->assertEquals($addressTypes[0]->getKey(), $jsonType->id);
        $this->assertEquals($addressTypes[0]->name, $jsonType->name);
    }

    public function test_update_address_type(): void
    {
        $type = AddressType::factory()->create(['name' => 'Home', 'client_id' => $this->dbClient->getKey()]);
        $payload = [
            'id' => $type->getKey(),
            'name' => 'Residential',
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/address-types', $payload);
        $response->assertStatus(200);

        $data = json_decode($response->getContent(), true)['data'];

        $this->assertEquals($type->id, $data['id']);
        $this->assertEquals('Residential', $data['name']);
        $this->assertDatabaseHas('address_types', [
            'id' => $type->id,
            'name' => 'Residential',
            'client_id' => $this->dbClient->getKey(),
        ]);
    }

    public function test_update_non_existent_address_type(): void
    {
        $payload = [
            'id' => 999,
            'name' => 'Test',
            'client_id' => $this->dbClient->getKey(),
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/address-types', $payload);
        $response->assertStatus(422);

        $this->assertDatabaseMissing('address_types', $payload);
    }
}
