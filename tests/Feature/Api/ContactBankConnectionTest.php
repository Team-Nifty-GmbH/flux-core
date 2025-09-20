<?php

use Carbon\Carbon;
use FluxErp\Models\Contact;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Models\Permission;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

uses(Illuminate\Foundation\Testing\WithFaker::class);

beforeEach(function (): void {
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
});

test('create contact bank connection', function (): void {
    $bankConnection = [
        'contact_id' => $this->contactBankConnections[0]->contact_id,
        'iban' => $this->faker->iban,
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/contact-bank-connections', $bankConnection);
    $response->assertCreated();

    $responseBankConnection = json_decode($response->getContent())->data;
    $dbBankConnection = ContactBankConnection::query()
        ->whereKey($responseBankConnection->id)
        ->first();

    expect($dbBankConnection)->not->toBeEmpty();
    expect($dbBankConnection->contact_id)->toEqual($bankConnection['contact_id']);
    expect($dbBankConnection->iban)->toEqual($bankConnection['iban']);
    expect($dbBankConnection->account_holder)->toBeNull();
    expect($dbBankConnection->bank_name)->toBeNull();
    expect($dbBankConnection->bic)->toBeNull();
    expect($this->user->is($dbBankConnection->getCreatedBy()))->toBeTrue();
    expect($this->user->is($dbBankConnection->getUpdatedBy()))->toBeTrue();
});

test('create contact bank connection maximum', function (): void {
    $bankConnection = [
        'contact_id' => $this->contactBankConnections[0]->contact_id,
        'iban' => $this->faker->iban('de'),
        'account_holder' => Str::random(),
        'bank_name' => Str::random(),
        'bic' => 'FAKEDE22',
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/contact-bank-connections', $bankConnection);
    $response->assertCreated();

    $responseBankConnection = json_decode($response->getContent())->data;
    $dbBankConnection = ContactBankConnection::query()
        ->whereKey($responseBankConnection->id)
        ->first();

    expect($dbBankConnection)->not->toBeEmpty();
    expect($dbBankConnection->contact_id)->toEqual($bankConnection['contact_id']);
    expect($dbBankConnection->iban)->toEqual($bankConnection['iban']);
    expect($dbBankConnection->account_holder)->toEqual($bankConnection['account_holder']);
    expect($dbBankConnection->bank_name)->toEqual($bankConnection['bank_name']);
    expect($dbBankConnection->bic)->toEqual($bankConnection['bic']);
    expect($this->user->is($dbBankConnection->getCreatedBy()))->toBeTrue();
    expect($this->user->is($dbBankConnection->getUpdatedBy()))->toBeTrue();
});

test('create contact bank connection validation fails', function (): void {
    $bankConnection = [
        'contact_id' => ++$this->contactBankConnections[2]->contact_id,
        'iban' => $this->faker->iban,
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/contact-bank-connections', $bankConnection);
    $response->assertUnprocessable();
});

test('delete contact bank connection', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)
        ->delete('/api/contact-bank-connections/' . $this->contactBankConnections[1]->id);
    $response->assertNoContent();

    $bankConnection = $this->contactBankConnections[1]->fresh();
    expect($bankConnection->deleted_at)->not->toBeNull();
    expect($this->user->is($bankConnection->getDeletedBy()))->toBeTrue();
});

test('delete contact bank connection bank connection not found', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)
        ->delete('/api/contact-bank-connections/' . ++$this->contactBankConnections[2]->id);
    $response->assertNotFound();
});

test('get contact bank connection', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)
        ->get('/api/contact-bank-connections/' . $this->contactBankConnections[0]->id);
    $response->assertOk();

    $json = json_decode($response->getContent());
    $jsonContactBankConnection = $json->data;

    expect($jsonContactBankConnection)->not->toBeEmpty();
    expect($jsonContactBankConnection->id)->toEqual($this->contactBankConnections[0]->id);
    expect($jsonContactBankConnection->contact_id)->toEqual($this->contactBankConnections[0]->contact_id);
    expect($jsonContactBankConnection->iban)->toEqual($this->contactBankConnections[0]->iban);
    expect($jsonContactBankConnection->account_holder)->toEqual($this->contactBankConnections[0]->account_holder);
    expect($jsonContactBankConnection->bank_name)->toEqual($this->contactBankConnections[0]->bank_name);
    expect($jsonContactBankConnection->bic)->toEqual($this->contactBankConnections[0]->bic);
    expect(Carbon::parse($jsonContactBankConnection->created_at))->toEqual(Carbon::parse($this->contactBankConnections[0]->created_at));
    expect(Carbon::parse($jsonContactBankConnection->updated_at))->toEqual(Carbon::parse($this->contactBankConnections[0]->updated_at));
});

test('get contact bank connection contact bank connection not found', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)
        ->get('/api/contact-bank-connections/' . ++$this->contactBankConnections[2]->id);
    $response->assertNotFound();
});

test('get contact bank connections', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/contact-bank-connections');
    $response->assertOk();

    $json = json_decode($response->getContent());
    $jsonContactBankConnections = collect($json->data->data);

    expect(count($jsonContactBankConnections))->toBeGreaterThanOrEqual(2);

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
});

test('update contact bank connection', function (): void {
    $bankConnection = [
        'id' => $this->contactBankConnections[0]->id,
        'bank_name' => uniqid(),
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/contact-bank-connections', $bankConnection);
    $response->assertOk();

    $responseBankConnection = json_decode($response->getContent())->data;
    $dbBankConnection = ContactBankConnection::query()
        ->whereKey($responseBankConnection->id)
        ->first();

    expect($dbBankConnection)->not->toBeEmpty();
    expect($dbBankConnection->id)->toEqual($bankConnection['id']);
    expect($dbBankConnection->bank_name)->toEqual($bankConnection['bank_name']);
    expect($this->user->is($dbBankConnection->getUpdatedBy()))->toBeTrue();
});

test('update contact bank connection maximum', function (): void {
    $bankConnection = [
        'id' => $this->contactBankConnections[0]->id,
        'contact_id' => $this->contactBankConnections[2]->contact_id,
        'iban' => $this->faker->iban(),
        'account_holder' => Str::random(),
        'bank_name' => Str::random(),
        'bic' => 'FAKEDE22',
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/contact-bank-connections', $bankConnection);
    $response->assertOk();

    $responseBankConnection = json_decode($response->getContent())->data;
    $dbBankConnection = ContactBankConnection::query()
        ->whereKey($responseBankConnection->id)
        ->first();

    expect($dbBankConnection)->not->toBeEmpty();
    expect($dbBankConnection->id)->toEqual($bankConnection['id']);
    expect($dbBankConnection->contact_id)->toEqual($bankConnection['contact_id']);
    expect($dbBankConnection->iban)->toEqual($bankConnection['iban']);
    expect($dbBankConnection->account_holder)->toEqual($bankConnection['account_holder']);
    expect($dbBankConnection->bank_name)->toEqual($bankConnection['bank_name']);
    expect($dbBankConnection->bic)->toEqual($bankConnection['bic']);
    expect($this->user->is($dbBankConnection->getUpdatedBy()))->toBeTrue();
});

test('update contact bank connection multi status validation fails', function (): void {
    $bankConnection = [
        'id' => $this->contactBankConnections[0]->id,
        'iban' => Str::random(),
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/contact-bank-connections', $bankConnection);
    $response->assertUnprocessable();

    $responseBankConnection = json_decode($response->getContent());
    expect($responseBankConnection->status)->toEqual(422);
});
