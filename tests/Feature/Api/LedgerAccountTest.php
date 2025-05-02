<?php

namespace FluxErp\Tests\Feature\Api;

use FluxErp\Enums\LedgerAccountTypeEnum;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;

class LedgerAccountTest extends BaseSetup
{
    use WithFaker;

    private Collection $ledgerAccounts;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ledgerAccounts = LedgerAccount::factory()->count(3)->create([
            'client_id' => $this->dbClient->getKey(),
            'number' => fn () => $this->faker->unique()->numberBetween(10, 20000),
            'name' => fn () => $this->faker->name,
            'ledger_account_type_enum' => fn () => $this->faker->randomElement(LedgerAccountTypeEnum::values()),
            'is_automatic' => fn () => $this->faker->boolean,
        ]);

        $this->permissions = [
            'index' => Permission::findOrCreate('api.ledger-accounts.get'),
            'show' => Permission::findOrCreate('api.ledger-accounts.{id}.get'),
            'create' => Permission::findOrCreate('api.ledger-accounts.post'),
            'update' => Permission::findOrCreate('api.ledger-accounts.put'),
            'delete' => Permission::findOrCreate('api.ledger-accounts.{id}.delete'),
        ];
    }

    public function test_create_ledger_account_rules_fail(): void
    {
        $payload = [];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/ledger-accounts', $payload);
        $response->assertStatus(422);
        $response = $response['data']['items'][0]['errors'];

        $this->assertArrayHasKey('number', $response);
        $this->assertArrayHasKey('name', $response);
        $this->assertArrayHasKey('ledger_account_type_enum', $response);
    }

    public function test_create_ledger_account_success(): void
    {
        $payload = [
            'client_id' => $this->dbClient->getKey(),
            'number' => $this->faker->numberBetween(10, 200),
            'name' => $this->faker->name,
            'ledger_account_type_enum' => $this->faker->randomElement(LedgerAccountTypeEnum::values()),
            'is_automatic' => $this->faker->boolean,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/ledger-accounts', $payload);
        $response->assertStatus(201);

        $data = $response->json('data');

        $dbEntry = $payload + ['id' => $data['id']];

        $this->assertDatabaseHas('ledger_accounts', $dbEntry);
    }

    public function test_create_ledger_account_validation_fail(): void
    {
        $payload = [
            'client_id' => $this->ledgerAccounts->first()->client_id,
            'number' => $this->ledgerAccounts->first()->number,
            'name' => $this->faker->name,
            'ledger_account_type_enum' => $this->ledgerAccounts->first()->ledger_account_type_enum->value,
            'is_automatic' => $this->faker->boolean,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/ledger-accounts', $payload);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['number']);
    }

    public function test_delete_ledger_account_success(): void
    {
        $ledgerAccount = $this->ledgerAccounts->last();

        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/ledger-accounts/' . $ledgerAccount->getKey());
        $response->assertStatus(204);

        $this->assertDatabaseMissing('ledger_accounts', ['id' => $ledgerAccount->getKey()]);
    }

    public function test_delete_nonexistent_ledger_account(): void
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $nonId = $this->ledgerAccounts->max('id') + 1;

        $response = $this->actingAs($this->user)->delete('/api/ledger-accounts/' . $nonId);
        $response->assertStatus(404);
    }

    public function test_index_ledger_accounts(): void
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/ledger-accounts');
        $response->assertStatus(200);

        $items = collect($response->json('data.data'));
        $this->assertCount($this->ledgerAccounts->count(), $items);

        foreach ($this->ledgerAccounts as $ledgerAccount) {
            $this->assertTrue(
                $items->contains(fn ($i) => $i['id'] === $ledgerAccount->getKey() &&
                    $i['client_id'] == $ledgerAccount->client_id &&
                    $i['number'] == $ledgerAccount->number &&
                    $i['name'] == $ledgerAccount->name &&
                    $i['description'] == $ledgerAccount->description &&
                    $i['ledger_account_type_enum'] == $ledgerAccount->ledger_account_type_enum->value &&
                    $i['is_automatic'] == $ledgerAccount->is_automatic
                )
            );
        }
    }

    public function test_show_ledger_account(): void
    {
        $ledgerAccount = $this->ledgerAccounts->first();

        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/ledger-accounts/' . $ledgerAccount->getKey());
        $response->assertStatus(200);

        $data = $response->json('data');

        $this->assertEquals($ledgerAccount->getKey(), $data['id']);
        $this->assertEquals($ledgerAccount->client_id, $data['client_id']);
        $this->assertEquals($ledgerAccount->number, $data['number']);
        $this->assertEquals($ledgerAccount->name, $data['name']);
        $this->assertEquals($ledgerAccount->description, $data['description']);
        $this->assertEquals($ledgerAccount->ledger_account_type_enum->value, $data['ledger_account_type_enum']);
        $this->assertEquals($ledgerAccount->is_automatic, $data['is_automatic']);
    }

    public function test_show_nonexistent_ledger_account(): void
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $nonId = $this->ledgerAccounts->max('id') + 1;

        $response = $this->actingAs($this->user)->get('/api/ledger-accounts/' . $nonId);
        $response->assertStatus(404);
    }

    public function test_update_ledger_account_success(): void
    {
        $ledgerAccount = $this->ledgerAccounts->first();
        $payload = [
            'id' => $ledgerAccount->getKey(),
            'name' => 'Testname',
            'number' => $this->faker->numberBetween(10, 20000),
            'description' => 'example_description',
            'ledger_account_type_enum' => LedgerAccountTypeEnum::Asset->value,
            'is_automatic' => false,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/ledger-accounts', $payload);
        $response->assertStatus(200);

        $this->assertDatabaseHas('ledger_accounts', $payload);
    }

    public function test_update_ledger_account_validation_fails(): void
    {
        $payload = [
            'client_id' => $this->ledgerAccounts->first()->client_id,
            'number' => $this->ledgerAccounts->first()->number,
            'name' => $this->faker->name,
            'ledger_account_type_enum' => $this->ledgerAccounts->first()->ledger_account_type_enum->value,
            'is_automatic' => $this->faker->boolean,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/ledger-accounts', $payload);
        $response->assertStatus(422);
    }

    public function test_update_nonexistent_ledger_account(): void
    {
        $nonId = $this->ledgerAccounts->max('id') + 1;

        $payload = [
            'id' => $nonId,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/ledger-accounts', $payload);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['id']);
    }
}
