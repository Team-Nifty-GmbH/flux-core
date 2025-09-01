<?php

uses(FluxErp\Tests\Feature\BaseSetup::class);
use Carbon\Carbon;
use FluxErp\Enums\SepaMandateTypeEnum;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Permission;
use FluxErp\Models\SepaMandate;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $dbClients = Client::factory()->count(2)->create();

    $paymentType = PaymentType::factory()
        ->hasAttached(factory: $dbClients[0], relationship: 'clients')
        ->create();

    $this->contacts = Contact::factory()->count(2)->create([
        'client_id' => $dbClients[0]->id,
        'payment_type_id' => $paymentType->id,
    ]);
    $this->contacts[] = Contact::factory()->create([
        'client_id' => $dbClients[1]->id,
    ]);

    $this->contactBankConnections = ContactBankConnection::factory()->count(2)->create([
        'contact_id' => $this->contacts[0]->id,
    ]);
    $this->contactBankConnections[] = ContactBankConnection::factory()->create([
        'contact_id' => $this->contacts[2]->id,
    ]);

    $this->sepaMandates = SepaMandate::factory()->count(2)->create([
        'client_id' => $dbClients[0]->id,
        'contact_id' => $this->contacts[0]->id,
        'contact_bank_connection_id' => $this->contactBankConnections[0]->id,
    ]);
    $this->sepaMandates[] = SepaMandate::factory()->create([
        'client_id' => $dbClients[1]->id,
        'contact_id' => $this->contacts[2]->id,
        'contact_bank_connection_id' => $this->contactBankConnections[2]->id,
    ]);

    $this->user->clients()->attach($dbClients->pluck('id')->toArray());

    $this->permissions = [
        'show' => Permission::findOrCreate('api.sepa-mandates.{id}.get'),
        'index' => Permission::findOrCreate('api.sepa-mandates.get'),
        'create' => Permission::findOrCreate('api.sepa-mandates.post'),
        'update' => Permission::findOrCreate('api.sepa-mandates.put'),
        'delete' => Permission::findOrCreate('api.sepa-mandates.{id}.delete'),
    ];
});

test('create sepa mandate', function (): void {
    $sepaMandate = [
        'client_id' => $this->sepaMandates[0]->client_id,
        'contact_id' => $this->contacts[0]->id,
        'contact_bank_connection_id' => $this->contactBankConnections[1]->id,
        'sepa_mandate_type_enum' => SepaMandateTypeEnum::BASIC->name,
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/sepa-mandates', $sepaMandate);
    $response->assertStatus(201);

    $responseSepaMandate = json_decode($response->getContent())->data;
    $dbSepaMandate = SepaMandate::query()
        ->whereKey($responseSepaMandate->id)
        ->first();

    expect($dbSepaMandate)->not->toBeEmpty();
    expect($dbSepaMandate->client_id)->toEqual($sepaMandate['client_id']);
    expect($dbSepaMandate->contact_id)->toEqual($sepaMandate['contact_id']);
    expect($dbSepaMandate->contact_bank_connection_id)->toEqual($sepaMandate['contact_bank_connection_id']);
    expect($dbSepaMandate->sepa_mandate_type_enum->name)->toEqual($sepaMandate['sepa_mandate_type_enum']);
    expect($dbSepaMandate->signed_date)->toBeNull();
    expect($this->user->is($dbSepaMandate->getCreatedBy()))->toBeTrue();
    expect($this->user->is($dbSepaMandate->getUpdatedBy()))->toBeTrue();
});

test('create sepa mandate client contact not exists', function (): void {
    $sepaMandate = [
        'client_id' => $this->sepaMandates[0]->client_id,
        'contact_id' => $this->contacts[2]->id,
        'contact_bank_connection_id' => $this->contactBankConnections[1]->id,
        'sepa_mandate_type_enum' => SepaMandateTypeEnum::B2B->name,
        'signed_date' => date('Y-m-d'),
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/sepa-mandates', $sepaMandate);
    $response->assertStatus(422);
});

test('create sepa mandate contact bank connection not exists', function (): void {
    $sepaMandate = [
        'client_id' => $this->sepaMandates[0]->client_id,
        'contact_id' => $this->contacts[0]->id,
        'contact_bank_connection_id' => $this->contactBankConnections[2]->id,
        'sepa_mandate_type_enum' => SepaMandateTypeEnum::BASIC->name,
        'signed_date' => date('Y-m-d'),
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/sepa-mandates', $sepaMandate);
    $response->assertStatus(422);
});

test('create sepa mandate maximum', function (): void {
    $sepaMandate = [
        'client_id' => $this->sepaMandates[0]->client_id,
        'contact_id' => $this->contacts[0]->id,
        'contact_bank_connection_id' => $this->contactBankConnections[1]->id,
        'sepa_mandate_type_enum' => SepaMandateTypeEnum::BASIC->name,
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

    expect($dbSepaMandate)->not->toBeEmpty();
    expect($dbSepaMandate->client_id)->toEqual($sepaMandate['client_id']);
    expect($dbSepaMandate->contact_id)->toEqual($sepaMandate['contact_id']);
    expect($dbSepaMandate->contact_bank_connection_id)->toEqual($sepaMandate['contact_bank_connection_id']);
    expect($dbSepaMandate->sepa_mandate_type_enum->name)->toEqual($sepaMandate['sepa_mandate_type_enum']);
    expect($dbSepaMandate->signed_date->toDateString())->toEqual($sepaMandate['signed_date']);
    expect($this->user->is($dbSepaMandate->getCreatedBy()))->toBeTrue();
    expect($this->user->is($dbSepaMandate->getUpdatedBy()))->toBeTrue();
});

test('create sepa mandate validation fails', function (): void {
    $sepaMandate = [
        'client_id' => $this->sepaMandates[0]->client_id,
        'contact_id' => 0,
        'contact_bank_connection_id' => 0,
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/sepa-mandates', $sepaMandate);
    $response->assertStatus(422);
    $response->assertJsonValidationErrorFor('contact_id');
    $response->assertJsonValidationErrorFor('contact_bank_connection_id');
    $response->assertJsonValidationErrorFor('sepa_mandate_type_enum');
});

test('delete sepa mandate', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/sepa-mandates/' . $this->sepaMandates[2]->id);
    $response->assertStatus(204);

    $sepaMandate = $this->sepaMandates[2]->fresh();
    expect($sepaMandate->deleted_at)->not->toBeNull();
    expect($this->user->is($sepaMandate->getDeletedBy()))->toBeTrue();
});

test('delete sepa mandate sepa mandate not found', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/sepa-mandates/' . ++$this->sepaMandates[2]->id);
    $response->assertStatus(404);
});

test('get sepa mandate', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/sepa-mandates/' . $this->sepaMandates[0]->id);
    $response->assertStatus(200);

    $json = json_decode($response->getContent());
    $jsonSepaMandate = $json->data;

    // Check if controller returns the test contact.
    expect($jsonSepaMandate)->not->toBeEmpty();
    expect($jsonSepaMandate->id)->toEqual($this->sepaMandates[0]->id);
    expect($jsonSepaMandate->client_id)->toEqual($this->sepaMandates[0]->client_id);
    expect($jsonSepaMandate->contact_id)->toEqual($this->sepaMandates[0]->contact_id);
    expect($jsonSepaMandate->contact_bank_connection_id)->toEqual($this->sepaMandates[0]->contact_bank_connection_id);
    expect($jsonSepaMandate->sepa_mandate_type_enum)->toEqual($this->sepaMandates[0]->sepa_mandate_type_enum->name);

    if (is_null($this->sepaMandates[0]->signed_date)) {
        expect($jsonSepaMandate->signed_date)->toBeNull();
    } else {
        expect(Carbon::parse($jsonSepaMandate->signed_date)->toDateString())->toEqual($this->sepaMandates[0]->signed_date->toDateString());
    }

    expect(Carbon::parse($jsonSepaMandate->created_at)->toDateTimeString())->toEqual($this->sepaMandates[0]->created_at->toDateTimeString());
    expect(Carbon::parse($jsonSepaMandate->updated_at)->toDateTimeString())->toEqual($this->sepaMandates[0]->updated_at->toDateTimeString());
});

test('get sepa mandate sepa mandate not found', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/sepa-mandates/' . ++$this->sepaMandates[2]->id);
    $response->assertStatus(404);
});

test('get sepa mandates', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/sepa-mandates');
    $response->assertStatus(200);

    $json = json_decode($response->getContent());
    $jsonSepaMandates = collect($json->data->data);

    // Check the amount of test contacts.
    expect(count($jsonSepaMandates))->toBeGreaterThanOrEqual(2);

    // Check if controller returns the test contacts.
    foreach ($this->sepaMandates as $sepaMandate) {
        $jsonSepaMandates->contains(function ($jsonSepaMandate) use ($sepaMandate) {
            return $jsonSepaMandate->id === $sepaMandate->id &&
                $jsonSepaMandate->client_id === $sepaMandate->client_id &&
                $jsonSepaMandate->contact_id === $sepaMandate->contact_id &&
                $jsonSepaMandate->contact_bank_connection_id === $sepaMandate->contact_bank_connection_id &&
                $jsonSepaMandate->sepa_mandate_type_enum === $sepaMandate->sepa_mandate_type_enum &&
                $jsonSepaMandate->signed_date === $sepaMandate->signed_date &&
                Carbon::parse($jsonSepaMandate->created_at) === Carbon::parse($sepaMandate->created_at) &&
                Carbon::parse($jsonSepaMandate->updated_at) === Carbon::parse($sepaMandate->updated_at);
        });
    }
});

test('update sepa mandate', function (): void {
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

    expect($dbSepaMandate)->not->toBeEmpty();
    expect($dbSepaMandate->id)->toEqual($sepaMandate['id']);
    expect($dbSepaMandate->signed_date->toDateString())->toEqual($sepaMandate['signed_date']);
    expect($this->user->is($dbSepaMandate->getUpdatedBy()))->toBeTrue();
});

test('update sepa mandate maximum', function (): void {
    $sepaMandate = [
        'id' => $this->sepaMandates[0]->id,
        'contact_bank_connection_id' => $this->contactBankConnections[1]->id,
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

    expect($dbSepaMandate)->not->toBeEmpty();
    expect($dbSepaMandate->id)->toEqual($sepaMandate['id']);
    expect($dbSepaMandate->client_id)->toEqual($this->sepaMandates[0]->client_id);
    expect($dbSepaMandate->contact_id)->toEqual($this->sepaMandates[0]->contact_id);
    expect($dbSepaMandate->contact_bank_connection_id)->toEqual($sepaMandate['contact_bank_connection_id']);
    expect($dbSepaMandate->signed_date->toDateString())->toEqual($sepaMandate['signed_date']);
    expect($this->user->is($dbSepaMandate->getUpdatedBy()))->toBeTrue();
});

test('update sepa mandate multi status contact bank connection not exists', function (): void {
    $sepaMandates = [
        [
            'id' => $this->sepaMandates[2]->id,
            'contact_bank_connection_id' => $this->contactBankConnections[0]->id,
        ],
        [
            'id' => $this->sepaMandates[1]->id,
            'contact_bank_connection_id' => $this->contactBankConnections[2]->id,
        ],
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/sepa-mandates', $sepaMandates);
    $response->assertStatus(422);

    $responses = json_decode($response->getContent())->data->items;
    expect($responses[0]->id)->toEqual($sepaMandates[0]['id']);
    expect($responses[0]->status)->toEqual(422);
    expect(property_exists($responses[0]->errors, 'contact_bank_connection_id'))->toBeTrue();
    expect($responses[1]->id)->toEqual($sepaMandates[1]['id']);
    expect($responses[1]->status)->toEqual(422);
    expect(property_exists($responses[1]->errors, 'contact_bank_connection_id'))->toBeTrue();
});

test('update sepa mandate validation fails', function (): void {
    $sepaMandate = [
        'id' => $this->sepaMandates[0]->id,
        'signed_date' => Str::random(),
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/sepa-mandates', $sepaMandate);
    $response->assertStatus(422);

    $responseSepaMandate = json_decode($response->getContent());
    expect($responseSepaMandate->status)->toEqual(422);
});
