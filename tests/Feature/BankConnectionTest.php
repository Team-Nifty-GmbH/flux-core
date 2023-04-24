<?php

namespace FluxErp\Tests\Feature;

use Carbon\Carbon;
use FluxErp\Models\BankConnection;
use FluxErp\Models\Contact;
use FluxErp\Models\Permission;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;

class BankConnectionTest extends BaseSetup
{
    use DatabaseTransactions, WithFaker;

    private Collection $bankConnections;

    private array $permissions;

    protected function setUp(): void
    {
        parent::setUp();

        $contacts = Contact::factory()->count(2)->create([
            'client_id' => $this->dbClient->id,
        ]);

        $this->bankConnections = BankConnection::factory()->count(2)->create([
            'contact_id' => $contacts[0]->id,
        ]);
        $this->bankConnections[] = BankConnection::factory()->create([
            'contact_id' => $contacts[1]->id,
        ]);

        $this->permissions = [
            'show' => Permission::findOrCreate('api.bank-connections.{id}.get'),
            'index' => Permission::findOrCreate('api.bank-connections.get'),
            'create' => Permission::findOrCreate('api.bank-connections.post'),
            'update' => Permission::findOrCreate('api.bank-connections.put'),
            'delete' => Permission::findOrCreate('api.bank-connections.{id}.delete'),
        ];

        $this->app->make(PermissionRegistrar::class)->registerPermissions();
    }

    public function test_get_bank_connection()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)
            ->get('/api/bank-connections/' . $this->bankConnections[0]->id);
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $jsonBankConnection = $json->data;

        $this->assertNotEmpty($jsonBankConnection);
        $this->assertEquals($this->bankConnections[0]->id, $jsonBankConnection->id);
        $this->assertEquals($this->bankConnections[0]->contact_id, $jsonBankConnection->contact_id);
        $this->assertEquals($this->bankConnections[0]->iban, $jsonBankConnection->iban);
        $this->assertEquals($this->bankConnections[0]->account_holder, $jsonBankConnection->account_holder);
        $this->assertEquals($this->bankConnections[0]->bank_name, $jsonBankConnection->bank_name);
        $this->assertEquals($this->bankConnections[0]->bic, $jsonBankConnection->bic);
        $this->assertEquals(Carbon::parse($this->bankConnections[0]->created_at),
            Carbon::parse($jsonBankConnection->created_at));
        $this->assertEquals(Carbon::parse($this->bankConnections[0]->updated_at),
            Carbon::parse($jsonBankConnection->updated_at));
    }

    public function test_get_bank_connection_bank_connection_not_found()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)
            ->get('/api/bank-connections/' . ++$this->bankConnections[2]->id);
        $response->assertStatus(404);
    }

    public function test_get_bank_connections()
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/bank-connections');
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $jsonBankConnections = collect($json->data->data);

        $this->assertGreaterThanOrEqual(2, count($jsonBankConnections));

        foreach ($this->bankConnections as $bankConnection) {
            $jsonBankConnections->contains(function ($jsonBankConnection) use ($bankConnection) {
                return $jsonBankConnection->id === $bankConnection->id &&
                    $jsonBankConnection->contact_id === $bankConnection->contact_id &&
                    $jsonBankConnection->iban === $bankConnection->iban &&
                    $jsonBankConnection->account_holder === $bankConnection->account_holder &&
                    $jsonBankConnection->bank_name === $bankConnection->bank_name &&
                    $jsonBankConnection->bic === $bankConnection->bic &&
                    Carbon::parse($jsonBankConnection->created_at) === Carbon::parse($bankConnection->created_at) &&
                    Carbon::parse($jsonBankConnection->updated_at) === Carbon::parse($bankConnection->updated_at);
            });
        }
    }

    public function test_create_bank_connection()
    {
        $bankConnection = [
            'contact_id' => $this->bankConnections[0]->contact_id,
            'iban' => $this->faker->iban,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/bank-connections', $bankConnection);
        $response->assertStatus(201);

        $responseBankConnection = json_decode($response->getContent())->data;
        $dbBankConnection = BankConnection::query()
            ->whereKey($responseBankConnection->id)
            ->first();

        $this->assertNotEmpty($dbBankConnection);
        $this->assertEquals($bankConnection['contact_id'], $dbBankConnection->contact_id);
        $this->assertEquals($bankConnection['iban'], $dbBankConnection->iban);
        $this->assertNull($dbBankConnection->account_holder);
        $this->assertNull($dbBankConnection->bank_name);
        $this->assertNull($dbBankConnection->bic);
        $this->assertEquals($this->user->id, $dbBankConnection->created_by->id);
        $this->assertEquals($this->user->id, $dbBankConnection->updated_by->id);
    }

    public function test_create_bank_connection_maximum()
    {
        $bankConnection = [
            'contact_id' => $this->bankConnections[0]->contact_id,
            'iban' => $this->faker->iban('de'),
            'account_holder' => Str::random(),
            'bank_name' => Str::random(),
            'bic' => Str::random(),
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/bank-connections', $bankConnection);
        $response->assertStatus(201);

        $responseBankConnection = json_decode($response->getContent())->data;
        $dbBankConnection = BankConnection::query()
            ->whereKey($responseBankConnection->id)
            ->first();

        $this->assertNotEmpty($dbBankConnection);
        $this->assertEquals($bankConnection['contact_id'], $dbBankConnection->contact_id);
        $this->assertEquals($bankConnection['iban'], $dbBankConnection->iban);
        $this->assertEquals($bankConnection['account_holder'], $dbBankConnection->account_holder);
        $this->assertEquals($bankConnection['bank_name'], $dbBankConnection->bank_name);
        $this->assertEquals($bankConnection['bic'], $dbBankConnection->bic);
        $this->assertEquals($this->user->id, $dbBankConnection->created_by->id);
        $this->assertEquals($this->user->id, $dbBankConnection->updated_by->id);
    }

    public function test_create_bank_connection_validation_fails()
    {
        $bankConnection = [
            'contact_id' => ++$this->bankConnections[2]->contact_id,
            'iban' => $this->faker->iban,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/bank-connections', $bankConnection);
        $response->assertStatus(422);
    }

    public function test_update_bank_connection()
    {
        $bankConnection = [
            'id' => $this->bankConnections[0]->id,
            'bank_name' => uniqid(),
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/bank-connections', $bankConnection);
        $response->assertStatus(200);

        $responseBankConnection = json_decode($response->getContent())->data;
        $dbBankConnection = BankConnection::query()
            ->whereKey($responseBankConnection->id)
            ->first();

        $this->assertNotEmpty($dbBankConnection);
        $this->assertEquals($bankConnection['id'], $dbBankConnection->id);
        $this->assertEquals($bankConnection['bank_name'], $dbBankConnection->bank_name);
        $this->assertEquals($this->user->id, $dbBankConnection->updated_by->id);
    }

    public function test_update_bank_connection_maximum()
    {
        $bankConnection = [
            'id' => $this->bankConnections[0]->id,
            'contact_id' => $this->bankConnections[2]->contact_id,
            'iban' => $this->faker->iban,
            'account_holder' => Str::random(),
            'bank_name' => Str::random(),
            'bic' => Str::random(),
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/bank-connections', $bankConnection);
        $response->assertStatus(200);

        $responseBankConnection = json_decode($response->getContent())->data;
        $dbBankConnection = BankConnection::query()
            ->whereKey($responseBankConnection->id)
            ->first();

        $this->assertNotEmpty($dbBankConnection);
        $this->assertEquals($bankConnection['id'], $dbBankConnection->id);
        $this->assertEquals($bankConnection['contact_id'], $dbBankConnection->contact_id);
        $this->assertEquals($bankConnection['iban'], $dbBankConnection->iban);
        $this->assertEquals($bankConnection['account_holder'], $dbBankConnection->account_holder);
        $this->assertEquals($bankConnection['bank_name'], $dbBankConnection->bank_name);
        $this->assertEquals($bankConnection['bic'], $dbBankConnection->bic);
        $this->assertEquals($this->user->id, $dbBankConnection->updated_by->id);
    }

    public function test_update_bank_connection_multi_status_validation_fails()
    {
        $bankConnection = [
            'id' => $this->bankConnections[0]->id,
            'iban' => Str::random(),
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/bank-connections', $bankConnection);
        $response->assertStatus(422);

        $responseBankConnection = json_decode($response->getContent());
        $this->assertEquals(422, $responseBankConnection->status);
    }

    public function test_delete_bank_connection()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)
            ->delete('/api/bank-connections/' . $this->bankConnections[1]->id);
        $response->assertStatus(204);

        $bankConnection = $this->bankConnections[1]->fresh();
        $this->assertNotNull($bankConnection->deleted_at);
        $this->assertEquals($this->user->id, $bankConnection->deleted_by->id);
    }

    public function test_delete_bank_connection_bankConnection_not_found()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)
            ->delete('/api/bank-connections/' . ++$this->bankConnections[2]->id);
        $response->assertStatus(404);
    }
}
