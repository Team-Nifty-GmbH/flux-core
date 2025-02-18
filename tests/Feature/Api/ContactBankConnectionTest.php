<?php

namespace FluxErp\Tests\Feature\Api;

use Carbon\Carbon;
use FluxErp\Models\Contact;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

class ContactBankConnectionTest extends BaseSetup
{
    use DatabaseTransactions, WithFaker;

    private Collection $contactBankConnections;

    private array $permissions;

    protected function setUp(): void
    {
        parent::setUp();

        $contacts = Contact::factory()->count(2)->create([
            'client_id' => $this->dbClient->getKey(),
        ]);

        $this->contactBankConnections = ContactBankConnection::factory()->count(2)->create([
            'contact_id' => $contacts[0]->id,
        ]);
        $this->contactBankConnections[] = ContactBankConnection::factory()->create([
            'contact_id' => $contacts[1]->id,
        ]);

        $this->permissions = [
            'show' => Permission::findOrCreate('api.contact-bank-connections.{id}.get'),
            'index' => Permission::findOrCreate('api.contact-bank-connections.get'),
            'create' => Permission::findOrCreate('api.contact-bank-connections.post'),
            'update' => Permission::findOrCreate('api.contact-bank-connections.put'),
            'delete' => Permission::findOrCreate('api.contact-bank-connections.{id}.delete'),
        ];
    }

    public function test_get_contact_bank_connection()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)
            ->get('/api/contact-bank-connections/' . $this->contactBankConnections[0]->id);
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $jsonContactBankConnection = $json->data;

        $this->assertNotEmpty($jsonContactBankConnection);
        $this->assertEquals($this->contactBankConnections[0]->id, $jsonContactBankConnection->id);
        $this->assertEquals($this->contactBankConnections[0]->contact_id, $jsonContactBankConnection->contact_id);
        $this->assertEquals($this->contactBankConnections[0]->iban, $jsonContactBankConnection->iban);
        $this->assertEquals($this->contactBankConnections[0]->account_holder, $jsonContactBankConnection->account_holder);
        $this->assertEquals($this->contactBankConnections[0]->bank_name, $jsonContactBankConnection->bank_name);
        $this->assertEquals($this->contactBankConnections[0]->bic, $jsonContactBankConnection->bic);
        $this->assertEquals(Carbon::parse($this->contactBankConnections[0]->created_at),
            Carbon::parse($jsonContactBankConnection->created_at));
        $this->assertEquals(Carbon::parse($this->contactBankConnections[0]->updated_at),
            Carbon::parse($jsonContactBankConnection->updated_at));
    }

    public function test_get_contact_bank_connection_contact_bank_connection_not_found()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)
            ->get('/api/contact-bank-connections/' . ++$this->contactBankConnections[2]->id);
        $response->assertStatus(404);
    }

    public function test_get_contact_bank_connections()
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/contact-bank-connections');
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $jsonContactBankConnections = collect($json->data->data);

        $this->assertGreaterThanOrEqual(2, count($jsonContactBankConnections));

        foreach ($this->contactBankConnections as $bankConnection) {
            $jsonContactBankConnections->contains(function ($jsonContactBankConnection) use ($bankConnection) {
                return $jsonContactBankConnection->id === $bankConnection->id &&
                    $jsonContactBankConnection->contact_id === $bankConnection->contact_id &&
                    $jsonContactBankConnection->iban === $bankConnection->iban &&
                    $jsonContactBankConnection->account_holder === $bankConnection->account_holder &&
                    $jsonContactBankConnection->bank_name === $bankConnection->bank_name &&
                    $jsonContactBankConnection->bic === $bankConnection->bic &&
                    Carbon::parse($jsonContactBankConnection->created_at) === Carbon::parse($bankConnection->created_at) &&
                    Carbon::parse($jsonContactBankConnection->updated_at) === Carbon::parse($bankConnection->updated_at);
            });
        }
    }

    public function test_create_contact_bank_connection()
    {
        $bankConnection = [
            'contact_id' => $this->contactBankConnections[0]->contact_id,
            'iban' => $this->faker->iban,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/contact-bank-connections', $bankConnection);
        $response->assertStatus(201);

        $responseBankConnection = json_decode($response->getContent())->data;
        $dbBankConnection = ContactBankConnection::query()
            ->whereKey($responseBankConnection->id)
            ->first();

        $this->assertNotEmpty($dbBankConnection);
        $this->assertEquals($bankConnection['contact_id'], $dbBankConnection->contact_id);
        $this->assertEquals($bankConnection['iban'], $dbBankConnection->iban);
        $this->assertNull($dbBankConnection->account_holder);
        $this->assertNull($dbBankConnection->bank_name);
        $this->assertNull($dbBankConnection->bic);
        $this->assertTrue($this->user->is($dbBankConnection->getCreatedBy()));
        $this->assertTrue($this->user->is($dbBankConnection->getUpdatedBy()));
    }

    public function test_create_contact_bank_connection_maximum()
    {
        $bankConnection = [
            'contact_id' => $this->contactBankConnections[0]->contact_id,
            'iban' => $this->faker->iban('de'),
            'account_holder' => Str::random(),
            'bank_name' => Str::random(),
            'bic' => Str::random(),
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/contact-bank-connections', $bankConnection);
        $response->assertStatus(201);

        $responseBankConnection = json_decode($response->getContent())->data;
        $dbBankConnection = ContactBankConnection::query()
            ->whereKey($responseBankConnection->id)
            ->first();

        $this->assertNotEmpty($dbBankConnection);
        $this->assertEquals($bankConnection['contact_id'], $dbBankConnection->contact_id);
        $this->assertEquals($bankConnection['iban'], $dbBankConnection->iban);
        $this->assertEquals($bankConnection['account_holder'], $dbBankConnection->account_holder);
        $this->assertEquals($bankConnection['bank_name'], $dbBankConnection->bank_name);
        $this->assertEquals($bankConnection['bic'], $dbBankConnection->bic);
        $this->assertTrue($this->user->is($dbBankConnection->getCreatedBy()));
        $this->assertTrue($this->user->is($dbBankConnection->getUpdatedBy()));
    }

    public function test_create_contact_bank_connection_validation_fails()
    {
        $bankConnection = [
            'contact_id' => ++$this->contactBankConnections[2]->contact_id,
            'iban' => $this->faker->iban,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/contact-bank-connections', $bankConnection);
        $response->assertStatus(422);
    }

    public function test_update_contact_bank_connection()
    {
        $bankConnection = [
            'id' => $this->contactBankConnections[0]->id,
            'bank_name' => uniqid(),
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/contact-bank-connections', $bankConnection);
        $response->assertStatus(200);

        $responseBankConnection = json_decode($response->getContent())->data;
        $dbBankConnection = ContactBankConnection::query()
            ->whereKey($responseBankConnection->id)
            ->first();

        $this->assertNotEmpty($dbBankConnection);
        $this->assertEquals($bankConnection['id'], $dbBankConnection->id);
        $this->assertEquals($bankConnection['bank_name'], $dbBankConnection->bank_name);
        $this->assertTrue($this->user->is($dbBankConnection->getUpdatedBy()));
    }

    public function test_update_contact_bank_connection_maximum()
    {
        $bankConnection = [
            'id' => $this->contactBankConnections[0]->id,
            'contact_id' => $this->contactBankConnections[2]->contact_id,
            'iban' => $this->faker->iban,
            'account_holder' => Str::random(),
            'bank_name' => Str::random(),
            'bic' => Str::random(),
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/contact-bank-connections', $bankConnection);
        $response->assertStatus(200);

        $responseBankConnection = json_decode($response->getContent())->data;
        $dbBankConnection = ContactBankConnection::query()
            ->whereKey($responseBankConnection->id)
            ->first();

        $this->assertNotEmpty($dbBankConnection);
        $this->assertEquals($bankConnection['id'], $dbBankConnection->id);
        $this->assertEquals($bankConnection['contact_id'], $dbBankConnection->contact_id);
        $this->assertEquals($bankConnection['iban'], $dbBankConnection->iban);
        $this->assertEquals($bankConnection['account_holder'], $dbBankConnection->account_holder);
        $this->assertEquals($bankConnection['bank_name'], $dbBankConnection->bank_name);
        $this->assertEquals($bankConnection['bic'], $dbBankConnection->bic);
        $this->assertTrue($this->user->is($dbBankConnection->getUpdatedBy()));
    }

    public function test_update_contact_bank_connection_multi_status_validation_fails()
    {
        $bankConnection = [
            'id' => $this->contactBankConnections[0]->id,
            'iban' => Str::random(),
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/contact-bank-connections', $bankConnection);
        $response->assertStatus(422);

        $responseBankConnection = json_decode($response->getContent());
        $this->assertEquals(422, $responseBankConnection->status);
    }

    public function test_delete_contact_bank_connection()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)
            ->delete('/api/contact-bank-connections/' . $this->contactBankConnections[1]->id);
        $response->assertStatus(204);

        $bankConnection = $this->contactBankConnections[1]->fresh();
        $this->assertNotNull($bankConnection->deleted_at);
        $this->assertTrue($this->user->is($bankConnection->getDeletedBy()));
    }

    public function test_delete_contact_bank_connection_bank_connection_not_found()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)
            ->delete('/api/contact-bank-connections/' . ++$this->contactBankConnections[2]->id);
        $response->assertStatus(404);
    }
}
