<?php

use FluxErp\Enums\CommunicationTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Client;
use FluxErp\Models\Communication;
use FluxErp\Models\Contact;
use FluxErp\Models\Permission;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $dbClient = Client::factory()->create();

    $this->contact = Contact::factory()->create([
        'client_id' => $dbClient->id,
    ]);

    Address::factory()->create([
        'client_id' => $dbClient->id,
        'contact_id' => $this->contact->id,
    ]);

    $this->communications = Communication::factory()->count(3)->create([
        'communication_type_enum' => CommunicationTypeEnum::Mail,
    ]);

    $this->communications->push(
        Communication::factory()->create([
            'communication_type_enum' => CommunicationTypeEnum::PhoneCall,
        ])
    );

    $this->user->clients()->attach($dbClient->id);

    $this->permissions = [
        'show' => Permission::findOrCreate('api.communications.{id}.get'),
        'index' => Permission::findOrCreate('api.communications.get'),
        'create' => Permission::findOrCreate('api.communications.post'),
        'update' => Permission::findOrCreate('api.communications.put'),
        'delete' => Permission::findOrCreate('api.communications.{id}.delete'),
    ];
});

test('communication auto assign functionality', function (): void {
    $communication = Communication::factory()->create([
        'communication_type_enum' => CommunicationTypeEnum::Mail,
    ]);

    expect(method_exists($communication, 'autoAssign'))->toBeTrue();
    expect($communication)->toBeInstanceOf(Communication::class);
});

test('communication date auto set', function (): void {
    $communication = new Communication([
        'communication_type_enum' => CommunicationTypeEnum::Mail,
        'subject' => 'Test Date Auto Set',
    ]);

    $communication->save();

    expect($communication->date)->not->toBeNull();
});

test('communication enum casting', function (): void {
    $communication = $this->communications[0];

    expect($communication->communication_type_enum)->toBeInstanceOf(CommunicationTypeEnum::class);
    expect($communication->communication_type_enum)->toEqual(CommunicationTypeEnum::Mail);
});

test('communication html to text conversion', function (): void {
    $communication = new Communication([
        'communication_type_enum' => CommunicationTypeEnum::Mail,
        'subject' => 'Test HTML to Text',
        'html_body' => '<p>This <strong>HTML</strong> should convert to text</p>',
    ]);

    $communication->save();

    expect($communication->text_body)->toEqual('This HTML should convert to text');
});

test('communication text body stripping', function (): void {
    $communication = new Communication([
        'communication_type_enum' => CommunicationTypeEnum::Mail,
        'subject' => 'Test HTML Stripping',
        'text_body' => '<p>This should be <strong>stripped</strong> of HTML</p>',
    ]);

    $communication->save();

    expect($communication->text_body)->toEqual('This should be stripped of HTML');
});

test('communication timeframe columns', function (): void {
    $timeframeColumns = Communication::timeframeColumns();

    $expectedColumns = [
        'date',
        'started_at',
        'ended_at',
        'created_at',
        'updated_at',
    ];

    expect($timeframeColumns)->toEqual($expectedColumns);
});

test('create communication', function (): void {
    $communication = [
        'communication_type_enum' => CommunicationTypeEnum::Mail->value,
        'subject' => 'Test Email Subject',
        'text_body' => 'This is a test email body',
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->post('/api/communications', $communication);
    $response->assertCreated();

    $responseCommunication = json_decode($response->getContent())->data;
    $dbCommunication = Communication::query()
        ->whereKey($responseCommunication->id)
        ->first();

    expect($dbCommunication)->not->toBeEmpty();
    expect($dbCommunication->communication_type_enum->value)->toEqual($communication['communication_type_enum']);
    expect($dbCommunication->subject)->toEqual($communication['subject']);
    expect($dbCommunication->text_body)->toEqual($communication['text_body']);
    expect($dbCommunication->date)->not->toBeNull();
    expect($this->user->is($dbCommunication->getCreatedBy()))->toBeTrue();
    expect($this->user->is($dbCommunication->getUpdatedBy()))->toBeTrue();
});

test('create communication maximum', function (): void {
    $communication = [
        'communication_type_enum' => CommunicationTypeEnum::Mail->value,
        'subject' => 'Full Test Email',
        'text_body' => 'This is the text body',
        'html_body' => '<p>This is the <strong>HTML</strong> body</p>',
        'from' => 'sender@example.com',
        'to' => [['email' => 'recipient@example.com', 'name' => 'Test Recipient']],
        'cc' => [['email' => 'cc@example.com', 'name' => 'CC Recipient']],
        'bcc' => [['email' => 'bcc@example.com', 'name' => 'BCC Recipient']],
        'date' => now()->toDateTimeString(),
        'started_at' => now()->toDateTimeString(),
        'ended_at' => now()->addMinutes(30)->toDateTimeString(),
        'is_seen' => true,
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->post('/api/communications', $communication);
    $response->assertCreated();

    $responseCommunication = json_decode($response->getContent())->data;
    $dbCommunication = Communication::query()
        ->whereKey($responseCommunication->id)
        ->first();

    expect($dbCommunication)->not->toBeEmpty();
    expect($dbCommunication->subject)->toEqual($communication['subject']);
    expect($dbCommunication->text_body)->toEqual($communication['text_body']);
    expect($dbCommunication->html_body)->toEqual($communication['html_body']);
    expect($dbCommunication->from)->toEqual($communication['from']);
    expect($dbCommunication->to)->toEqual($communication['to']);
    expect($dbCommunication->cc)->toEqual($communication['cc']);
    expect($dbCommunication->bcc)->toEqual($communication['bcc']);
    expect($dbCommunication->is_seen)->toEqual($communication['is_seen']);
});

test('create communication validation fails', function (): void {
    $communication = [
        'communication_type_enum' => 'invalid_type',
        'subject' => '',
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->post('/api/communications', $communication);
    $response->assertUnprocessable();

    $response->assertJsonValidationErrors([
        'communication_type_enum',
    ]);
});

test('delete communication', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->delete('/api/communications/' . $this->communications[0]->id);
    $response->assertNoContent();

    $communication = $this->communications[0]->fresh();
    expect($communication->deleted_at)->not->toBeNull();
    expect($this->user->is($communication->getDeletedBy()))->toBeTrue();
});

test('delete communication not found', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->delete('/api/communications/' . (Communication::max('id') + 1));
    $response->assertNotFound();
});

test('get communication', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->get('/api/communications/' . $this->communications[0]->id);
    $response->assertOk();

    $json = json_decode($response->getContent());
    $jsonCommunication = $json->data;

    expect($jsonCommunication)->not->toBeEmpty();
    expect($jsonCommunication->id)->toEqual($this->communications[0]->id);
    expect($jsonCommunication->communication_type_enum)->toEqual($this->communications[0]->communication_type_enum->value);
    expect($jsonCommunication->subject)->toEqual($this->communications[0]->subject);
    expect($jsonCommunication->text_body)->toEqual($this->communications[0]->text_body);
    expect($jsonCommunication->is_seen)->toEqual($this->communications[0]->is_seen);
});

test('get communication not found', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->get('/api/communications/' . (Communication::max('id') + 1));
    $response->assertNotFound();
});

test('get communications', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->get('/api/communications');
    $response->assertOk();

    $json = json_decode($response->getContent());
    $jsonCommunications = collect($json->data->data);

    expect(count($jsonCommunications))->toBeGreaterThanOrEqual(4);

    foreach ($this->communications as $communication) {
        $jsonCommunications->contains(function ($jsonCommunication) use ($communication) {
            return $jsonCommunication->id === $communication->id &&
                $jsonCommunication->communication_type_enum === $communication->communication_type_enum->value &&
                $jsonCommunication->subject === $communication->subject &&
                $jsonCommunication->is_seen === $communication->is_seen;
        });
    }
});

test('update communication', function (): void {
    $communication = [
        'id' => $this->communications[0]->id,
        'subject' => 'Updated Subject',
        'is_seen' => true,
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->put('/api/communications', $communication);
    $response->assertOk();

    $responseCommunication = json_decode($response->getContent())->data;
    $dbCommunication = Communication::query()
        ->whereKey($responseCommunication->id)
        ->first();

    expect($dbCommunication)->not->toBeEmpty();
    expect($dbCommunication->id)->toEqual($communication['id']);
    expect($dbCommunication->subject)->toEqual($communication['subject']);
    expect($dbCommunication->is_seen)->toEqual($communication['is_seen']);
    expect($this->user->is($dbCommunication->getUpdatedBy()))->toBeTrue();
});

test('update communication maximum', function (): void {
    $communication = [
        'id' => $this->communications[1]->id,
        'subject' => 'Fully Updated Communication',
        'text_body' => 'This is the updated text body',
        'html_body' => '<p>This is the updated <em>HTML</em> body</p>',
        'from' => 'updated@example.com',
        'to' => [['email' => 'updated-recipient@example.com', 'name' => 'Updated Recipient']],
        'started_at' => now()->toDateTimeString(),
        'ended_at' => now()->addMinutes(30)->toDateTimeString(),
        'is_seen' => false,
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->put('/api/communications', $communication);
    $response->assertOk();

    $responseCommunication = json_decode($response->getContent())->data;
    $dbCommunication = Communication::query()
        ->whereKey($responseCommunication->id)
        ->first();

    expect($dbCommunication)->not->toBeEmpty();
    expect($dbCommunication->subject)->toEqual($communication['subject']);
    expect($dbCommunication->text_body)->toEqual($communication['text_body']);
    expect($dbCommunication->html_body)->toEqual($communication['html_body']);
    expect($dbCommunication->from)->toEqual($communication['from']);
    expect($dbCommunication->to)->toEqual($communication['to']);
    expect($dbCommunication->is_seen)->toEqual($communication['is_seen']);
});
