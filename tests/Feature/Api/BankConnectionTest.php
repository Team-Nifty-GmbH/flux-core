<?php

namespace FluxErp\Tests\Feature\Api;

use FluxErp\Models\BankConnection;
use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Laravel\Sanctum\Sanctum;

class BankConnectionTest extends BaseSetup
{
    private array $permissions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->permissions = [
            'show' => Permission::findOrCreate('api.bank-connections.{id}.get'),
            'index' => Permission::findOrCreate('api.bank-connections.get'),
            'create' => Permission::findOrCreate('api.bank-connections.post'),
            'update' => Permission::findOrCreate('api.bank-connections.put'),
            'delete' => Permission::findOrCreate('api.bank-connections.{id}.delete'),
        ];
    }

    public function test_create_bank_connection(): void
    {
        $payload = [
            'name' => 'Test Name',
            'account_holder' => 'Test Holder',
            'bank_name' => 'Test Bank',
            'iban' => 'DE75512108001245126199',
            'bic' => '72tjrv12j4a',
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/bank-connections', $payload);
        $response->assertStatus(201);

        $this->assertDatabaseHas('bank_connections', $payload);
    }

    public function test_create_bank_connection_with_invalid_iban(): void
    {
        $payload = [
            'iban' => 'invalidIBAN',
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/bank-connections', $payload);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['iban']);
    }

    public function test_delete_bank_connection(): void
    {
        $bankConnection = BankConnection::factory()->create();

        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/bank-connections/' . $bankConnection->id);
        $response->assertStatus(204);

        $this->assertDatabaseMissing('bank_connections', ['id' => $bankConnection->id]);
    }

    public function test_get_bank_connections(): void
    {
        $bankConnections = BankConnection::factory()->count(3)->create();

        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/bank-connections');
        $response->assertStatus(200);

        $jsonData = collect(json_decode($response->getContent())->data->data);

        $this->assertCount(3, $bankConnections);

        foreach ($bankConnections as $bankConnection) {
            $this->assertTrue($jsonData->contains(fn ($item) => $item->id === $bankConnection->id &&
                $item->name === $bankConnection->name
            ));
        }
    }

    public function test_get_non_existent_bank_connection(): void
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/bank-connections/' . '1');
        $response->assertStatus(404);
    }

    public function test_get_specific_bank_connection(): void
    {
        $bankConnections = BankConnection::factory()->count(2)->create();

        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/bank-connections/' . $bankConnections[0]->id);
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $jsonData = $json->data;

        $this->assertNotEmpty($jsonData);
        $this->assertEquals($bankConnections[0]->id, $jsonData->id);
        $this->assertEquals($bankConnections[0]->name, $jsonData->name);
    }

    public function test_update_bank_connection(): void
    {
        $bankConnection = BankConnection::factory()->create(['name' => 'Test Name']);
        $payload = [
            'id' => $bankConnection->id,
            'name' => 'Updated Test Name',
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/bank-connections', $payload);
        $response->assertStatus(200);

        $this->assertDatabaseHas('bank_connections', $payload);
    }

    public function test_update_bank_connection_not_found(): void
    {
        $payload = [
            'id' => 999,
            'name' => 'Test Bank Connection',
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/bank-connections', $payload);
        $response->assertStatus(422);

        $this->assertDatabaseMissing('bank_connections', $payload);
    }
}
