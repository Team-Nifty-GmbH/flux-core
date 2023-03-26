<?php

namespace FluxErp\Tests\Feature;

use FluxErp\Models\BankConnection;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Permission;
use FluxErp\Models\SepaMandate;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;

class SepaMandateTest extends BaseSetup
{
    use DatabaseTransactions;

    private Collection $contacts;

    private Collection $bankConnections;

    private Collection $sepaMandates;

    private array $permissions;

    protected function setUp(): void
    {
        parent::setUp();

        $dbClients = Client::factory()->count(2)->create();

        $paymentType = PaymentType::factory()->create([
            'client_id' => $dbClients[0]->id,
        ]);

        $this->contacts = Contact::factory()->count(2)->create([
            'client_id' => $dbClients[0]->id,
            'payment_type_id' => $paymentType->id,
        ]);
        $this->contacts[] = Contact::factory()->create([
            'client_id' => $dbClients[1]->id,
        ]);

        $this->bankConnections = BankConnection::factory()->count(2)->create([
            'contact_id' => $this->contacts[0]->id,
        ]);
        $this->bankConnections[] = BankConnection::factory()->create([
            'contact_id' => $this->contacts[2]->id,
        ]);

        $this->sepaMandates = SepaMandate::factory()->count(2)->create([
            'client_id' => $dbClients[0]->id,
            'contact_id' => $this->contacts[0]->id,
            'bank_connection_id' => $this->bankConnections[0]->id,
        ]);
        $this->sepaMandates[] = SepaMandate::factory()->create([
            'client_id' => $dbClients[1]->id,
            'contact_id' => $this->contacts[2]->id,
            'bank_connection_id' => $this->bankConnections[2]->id,
        ]);

        $this->permissions = [
            'show' => Permission::findOrCreate('api.sepa-mandates.{id}.get'),
            'index' => Permission::findOrCreate('api.sepa-mandates.get'),
            'create' => Permission::findOrCreate('api.sepa-mandates.post'),
            'update' => Permission::findOrCreate('api.sepa-mandates.put'),
            'delete' => Permission::findOrCreate('api.sepa-mandates.{id}.delete'),
        ];

        $this->app->make(PermissionRegistrar::class)->registerPermissions();
    }

    public function test_get_sepa_mandate()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/sepa-mandates/' . $this->sepaMandates[0]->id);
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $jsonSepaMandate = $json->data;

        // Check if controller returns the test contact.
        $this->assertNotEmpty($jsonSepaMandate);
        $this->assertEquals($this->sepaMandates[0]->id, $jsonSepaMandate->id);
        $this->assertEquals($this->sepaMandates[0]->client_id, $jsonSepaMandate->client_id);
        $this->assertEquals($this->sepaMandates[0]->contact_id, $jsonSepaMandate->contact_id);
        $this->assertEquals($this->sepaMandates[0]->bank_connection_id,
            $jsonSepaMandate->bank_connection_id);
        $this->assertEquals($this->sepaMandates[0]->signed_date, $jsonSepaMandate->signed_date);
        $this->assertEquals(Carbon::parse($this->sepaMandates[0]->created_at),
            Carbon::parse($jsonSepaMandate->created_at));
        $this->assertEquals(Carbon::parse($this->sepaMandates[0]->updated_at),
            Carbon::parse($jsonSepaMandate->updated_at));
    }

    public function test_get_sepa_mandate_sepa_mandate_not_found()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/sepa-mandates/' . ++$this->sepaMandates[2]->id);
        $response->assertStatus(404);
    }

    public function test_get_sepa_mandates()
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/sepa-mandates');
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $jsonSepaMandates = collect($json->data->data);

        // Check the amount of test contacts.
        $this->assertGreaterThanOrEqual(2, count($jsonSepaMandates));

        // Check if controller returns the test contacts.
        foreach ($this->sepaMandates as $sepaMandate) {
            $jsonSepaMandates->contains(function ($jsonSepaMandate) use ($sepaMandate) {
                return $jsonSepaMandate->id === $sepaMandate->id &&
                    $jsonSepaMandate->client_id === $sepaMandate->client_id &&
                    $jsonSepaMandate->contact_id === $sepaMandate->contact_id &&
                    $jsonSepaMandate->bank_connection_id === $sepaMandate->bank_connection_id &&
                    $jsonSepaMandate->signed_date === $sepaMandate->signed_date &&
                    Carbon::parse($jsonSepaMandate->created_at) === Carbon::parse($sepaMandate->created_at) &&
                    Carbon::parse($jsonSepaMandate->updated_at) === Carbon::parse($sepaMandate->updated_at);
            });
        }
    }

    public function test_create_sepa_mandate()
    {
        $sepaMandate = [
            'client_id' => $this->sepaMandates[0]->client_id,
            'contact_id' => $this->contacts[0]->id,
            'bank_connection_id' => $this->bankConnections[1]->id,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/sepa-mandates', $sepaMandate);
        $response->assertStatus(201);

        $responseSepaMandate = json_decode($response->getContent())->data;
        $dbSepaMandate = SepaMandate::query()
            ->whereKey($responseSepaMandate->id)
            ->first();

        $this->assertNotEmpty($dbSepaMandate);
        $this->assertEquals($sepaMandate['client_id'], $dbSepaMandate->client_id);
        $this->assertEquals($sepaMandate['contact_id'], $dbSepaMandate->contact_id);
        $this->assertEquals($sepaMandate['bank_connection_id'], $dbSepaMandate->bank_connection_id);
        $this->assertNull($dbSepaMandate->signed_date);
        $this->assertEquals($this->user->id, $dbSepaMandate->created_by->id);
        $this->assertEquals($this->user->id, $dbSepaMandate->updated_by->id);
    }

    public function test_create_sepa_mandate_maximum()
    {
        $sepaMandate = [
            'client_id' => $this->sepaMandates[0]->client_id,
            'contact_id' => $this->contacts[0]->id,
            'bank_connection_id' => $this->bankConnections[1]->id,
            'signed_date' => date('Y-m-d'),
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/sepa-mandates', $sepaMandate);
        $response->assertStatus(201);

        $responseSepaMandate = json_decode($response->getContent())->data;
        $dbSepaMandate = SepaMandate::query()
            ->whereKey($responseSepaMandate->id)
            ->first();

        $this->assertNotEmpty($dbSepaMandate);
        $this->assertEquals($sepaMandate['client_id'], $dbSepaMandate->client_id);
        $this->assertEquals($sepaMandate['contact_id'], $dbSepaMandate->contact_id);
        $this->assertEquals($sepaMandate['bank_connection_id'], $dbSepaMandate->bank_connection_id);
        $this->assertEquals($sepaMandate['signed_date'], $dbSepaMandate->signed_date);
        $this->assertEquals($this->user->id, $dbSepaMandate->created_by->id);
        $this->assertEquals($this->user->id, $dbSepaMandate->updated_by->id);
    }

    public function test_create_sepa_mandate_validation_fails()
    {
        $sepaMandate = [
            'client_id' => $this->sepaMandates[0]->client_id,
            'contact_id' => 0,
            'bank_connection_id' => 0,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/sepa-mandates', $sepaMandate);
        $response->assertStatus(422);
    }

    public function test_create_sepa_mandate_client_contact_not_exists()
    {
        $sepaMandate = [
            'client_id' => $this->sepaMandates[0]->client_id,
            'contact_id' => $this->contacts[2]->id,
            'bank_connection_id' => $this->bankConnections[1]->id,
            'signed_date' => date('Y-m-d'),
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/sepa-mandates', $sepaMandate);
        $response->assertStatus(409);
    }

    public function test_create_sepa_mandate_contact_bank_connection_not_exists()
    {
        $sepaMandate = [
            'client_id' => $this->sepaMandates[0]->client_id,
            'contact_id' => $this->contacts[0]->id,
            'bank_connection_id' => $this->bankConnections[2]->id,
            'signed_date' => date('Y-m-d'),
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/sepa-mandates', $sepaMandate);
        $response->assertStatus(409);
    }

    public function test_update_sepa_mandate()
    {
        $sepaMandate = [
            'id' => $this->sepaMandates[0]->id,
            'signed_date' => date('Y-m-d'),
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/sepa-mandates', $sepaMandate);
        $response->assertStatus(200);

        $responseSepaMandate = json_decode($response->getContent())->data;
        $dbSepaMandate = SepaMandate::query()
            ->whereKey($responseSepaMandate->id)
            ->first();

        $this->assertNotEmpty($dbSepaMandate);
        $this->assertEquals($sepaMandate['id'], $dbSepaMandate->id);
        $this->assertEquals($sepaMandate['signed_date'], $dbSepaMandate->signed_date);
        $this->assertEquals($this->user->id, $dbSepaMandate->updated_by->id);
    }

    public function test_update_sepa_mandate_maximum()
    {
        $sepaMandate = [
            'id' => $this->sepaMandates[0]->id,
            'client_id' => $this->sepaMandates[2]->client_id,
            'contact_id' => $this->contacts[2]->id,
            'bank_connection_id' => $this->bankConnections[2]->id,
            'signed_date' => date('Y-m-d'),
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/sepa-mandates', $sepaMandate);
        $response->assertStatus(200);

        $responseSepaMandate = json_decode($response->getContent())->data;
        $dbSepaMandate = SepaMandate::query()
            ->whereKey($responseSepaMandate->id)
            ->first();

        $this->assertNotEmpty($dbSepaMandate);
        $this->assertEquals($sepaMandate['id'], $dbSepaMandate->id);
        $this->assertEquals($sepaMandate['client_id'], $dbSepaMandate->client_id);
        $this->assertEquals($sepaMandate['contact_id'], $dbSepaMandate->contact_id);
        $this->assertEquals($sepaMandate['bank_connection_id'], $dbSepaMandate->bank_connection_id);
        $this->assertEquals($sepaMandate['signed_date'], $dbSepaMandate->signed_date);
        $this->assertEquals($this->user->id, $dbSepaMandate->updated_by->id);
    }

    public function test_update_sepa_mandate_multi_status_validation_fails()
    {
        $sepaMandate = [
            'id' => $this->sepaMandates[0]->id,
            'signed_date' => Str::random(),
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/sepa-mandates', $sepaMandate);
        $response->assertStatus(422);

        $responseSepaMandate = json_decode($response->getContent());
        $this->assertEquals($sepaMandate['id'], $responseSepaMandate->id);
        $this->assertEquals(422, $responseSepaMandate->status);
    }

    public function test_update_sepa_mandate_multi_status_client_contact_bank_connection_not_exists()
    {
        $sepaMandates = [
            [
                'id' => $this->sepaMandates[0]->id,
                'client_id' => $this->sepaMandates[2]->client_id,
            ],
            [
                'id' => $this->sepaMandates[1]->id,
                'contact_id' => $this->contacts[2]->id,
            ],
            [
                'id' => $this->sepaMandates[0]->id,
                'client_id' => $this->sepaMandates[2]->client_id,
                'contact_id' => $this->contacts[1]->id,
            ],
            [
                'id' => $this->sepaMandates[2]->id,
                'bank_connection_id' => $this->bankConnections[0]->id,
            ],
            [
                'id' => $this->sepaMandates[1]->id,
                'contact_id' => $this->contacts[2]->id,
                'bank_connection_id' => $this->bankConnections[1]->id,
            ],
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/sepa-mandates', $sepaMandates);
        $response->assertStatus(422);

        $responses = json_decode($response->getContent())->responses;
        $this->assertEquals($sepaMandates[0]['id'], $responses[0]->id);
        $this->assertEquals(409, $responses[0]->status);
        $this->assertTrue(property_exists($responses[0]->errors, 'contact_id'));
        $this->assertEquals($sepaMandates[1]['id'], $responses[1]->id);
        $this->assertEquals(409, $responses[1]->status);
        $this->assertTrue(property_exists($responses[1]->errors, 'contact_id'));
        $this->assertTrue(property_exists($responses[1]->errors, 'bank_connection_id'));
        $this->assertEquals($sepaMandates[2]['id'], $responses[2]->id);
        $this->assertEquals(409, $responses[2]->status);
        $this->assertTrue(property_exists($responses[2]->errors, 'contact_id'));
        $this->assertEquals($sepaMandates[3]['id'], $responses[3]->id);
        $this->assertEquals(409, $responses[3]->status);
        $this->assertTrue(property_exists($responses[3]->errors, 'bank_connection_id'));
        $this->assertEquals($sepaMandates[4]['id'], $responses[4]->id);
        $this->assertEquals(409, $responses[4]->status);
        $this->assertTrue(property_exists($responses[4]->errors, 'bank_connection_id'));
    }

    public function test_delete_sepa_mandate()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/sepa-mandates/' . $this->sepaMandates[2]->id);
        $response->assertStatus(204);

        $sepaMandate = $this->sepaMandates[2]->fresh();
        $this->assertNotNull($sepaMandate->deleted_at);
        $this->assertEquals($this->user->id, $sepaMandate->deleted_by->id);
    }

    public function test_delete_sepa_mandate_sepa_mandate_not_found()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/sepa-mandates/' . ++$this->sepaMandates[2]->id);
        $response->assertStatus(404);
    }
}
