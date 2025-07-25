<?php

namespace FluxErp\Tests\Feature\Api;

use FluxErp\Enums\CommunicationTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Client;
use FluxErp\Models\Communication;
use FluxErp\Models\Contact;
use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Support\Collection;
use Laravel\Sanctum\Sanctum;

class CommunicationTest extends BaseSetup
{
    private Collection $communications;

    private Contact $contact;

    private array $permissions;

    protected function setUp(): void
    {
        parent::setUp();

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
    }

    public function test_communication_auto_assign_functionality(): void
    {
        $communication = Communication::factory()->create([
            'communication_type_enum' => CommunicationTypeEnum::Mail,
        ]);

        $this->assertTrue(method_exists($communication, 'autoAssign'));
        $this->assertInstanceOf(Communication::class, $communication);
    }

    public function test_communication_date_auto_set(): void
    {
        $communication = new Communication([
            'communication_type_enum' => CommunicationTypeEnum::Mail,
            'subject' => 'Test Date Auto Set',
        ]);

        $communication->save();

        $this->assertNotNull($communication->date);
    }

    public function test_communication_enum_casting(): void
    {
        $communication = $this->communications[0];

        $this->assertInstanceOf(CommunicationTypeEnum::class, $communication->communication_type_enum);
        $this->assertEquals(CommunicationTypeEnum::Mail, $communication->communication_type_enum);
    }

    public function test_communication_html_to_text_conversion(): void
    {
        $communication = new Communication([
            'communication_type_enum' => CommunicationTypeEnum::Mail,
            'subject' => 'Test HTML to Text',
            'html_body' => '<p>This <strong>HTML</strong> should convert to text</p>',
        ]);

        $communication->save();

        $this->assertEquals('This HTML should convert to text', $communication->text_body);
    }

    public function test_communication_text_body_stripping(): void
    {
        $communication = new Communication([
            'communication_type_enum' => CommunicationTypeEnum::Mail,
            'subject' => 'Test HTML Stripping',
            'text_body' => '<p>This should be <strong>stripped</strong> of HTML</p>',
        ]);

        $communication->save();

        $this->assertEquals('This should be stripped of HTML', $communication->text_body);
    }

    public function test_communication_timeframe_columns(): void
    {
        $timeframeColumns = Communication::timeframeColumns();

        $expectedColumns = [
            'date',
            'started_at',
            'ended_at',
            'created_at',
            'updated_at',
        ];

        $this->assertEquals($expectedColumns, $timeframeColumns);
    }

    public function test_create_communication(): void
    {
        $communication = [
            'communication_type_enum' => CommunicationTypeEnum::Mail->value,
            'subject' => 'Test Email Subject',
            'text_body' => 'This is a test email body',
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->post('/api/communications', $communication);
        $response->assertStatus(201);

        $responseCommunication = json_decode($response->getContent())->data;
        $dbCommunication = Communication::query()
            ->whereKey($responseCommunication->id)
            ->first();

        $this->assertNotEmpty($dbCommunication);
        $this->assertEquals($communication['communication_type_enum'], $dbCommunication->communication_type_enum->value);
        $this->assertEquals($communication['subject'], $dbCommunication->subject);
        $this->assertEquals($communication['text_body'], $dbCommunication->text_body);
        $this->assertNotNull($dbCommunication->date);
        $this->assertTrue($this->user->is($dbCommunication->getCreatedBy()));
        $this->assertTrue($this->user->is($dbCommunication->getUpdatedBy()));
    }

    public function test_create_communication_maximum(): void
    {
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
        $response->assertStatus(201);

        $responseCommunication = json_decode($response->getContent())->data;
        $dbCommunication = Communication::query()
            ->whereKey($responseCommunication->id)
            ->first();

        $this->assertNotEmpty($dbCommunication);
        $this->assertEquals($communication['subject'], $dbCommunication->subject);
        $this->assertEquals($communication['text_body'], $dbCommunication->text_body);
        $this->assertEquals($communication['html_body'], $dbCommunication->html_body);
        $this->assertEquals($communication['from'], $dbCommunication->from);
        $this->assertEquals($communication['to'], $dbCommunication->to);
        $this->assertEquals($communication['cc'], $dbCommunication->cc);
        $this->assertEquals($communication['bcc'], $dbCommunication->bcc);
        $this->assertEquals($communication['is_seen'], $dbCommunication->is_seen);
    }

    public function test_create_communication_validation_fails(): void
    {
        $communication = [
            'communication_type_enum' => 'invalid_type',
            'subject' => '',
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->post('/api/communications', $communication);
        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'communication_type_enum',
        ]);
    }

    public function test_delete_communication(): void
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->delete('/api/communications/' . $this->communications[0]->id);
        $response->assertStatus(204);

        $communication = $this->communications[0]->fresh();
        $this->assertNotNull($communication->deleted_at);
        $this->assertTrue($this->user->is($communication->getDeletedBy()));
    }

    public function test_delete_communication_not_found(): void
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->delete('/api/communications/' . (Communication::max('id') + 1));
        $response->assertStatus(404);
    }

    public function test_get_communication(): void
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->get('/api/communications/' . $this->communications[0]->id);
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $jsonCommunication = $json->data;

        $this->assertNotEmpty($jsonCommunication);
        $this->assertEquals($this->communications[0]->id, $jsonCommunication->id);
        $this->assertEquals($this->communications[0]->communication_type_enum->value, $jsonCommunication->communication_type_enum);
        $this->assertEquals($this->communications[0]->subject, $jsonCommunication->subject);
        $this->assertEquals($this->communications[0]->text_body, $jsonCommunication->text_body);
        $this->assertEquals($this->communications[0]->is_seen, $jsonCommunication->is_seen);
    }

    public function test_get_communication_not_found(): void
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->get('/api/communications/' . (Communication::max('id') + 1));
        $response->assertStatus(404);
    }

    public function test_get_communications(): void
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->get('/api/communications');
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $jsonCommunications = collect($json->data->data);

        $this->assertGreaterThanOrEqual(4, count($jsonCommunications));

        foreach ($this->communications as $communication) {
            $jsonCommunications->contains(function ($jsonCommunication) use ($communication) {
                return $jsonCommunication->id === $communication->id &&
                    $jsonCommunication->communication_type_enum === $communication->communication_type_enum->value &&
                    $jsonCommunication->subject === $communication->subject &&
                    $jsonCommunication->is_seen === $communication->is_seen;
            });
        }
    }

    public function test_update_communication(): void
    {
        $communication = [
            'id' => $this->communications[0]->id,
            'subject' => 'Updated Subject',
            'is_seen' => true,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->put('/api/communications', $communication);
        $response->assertStatus(200);

        $responseCommunication = json_decode($response->getContent())->data;
        $dbCommunication = Communication::query()
            ->whereKey($responseCommunication->id)
            ->first();

        $this->assertNotEmpty($dbCommunication);
        $this->assertEquals($communication['id'], $dbCommunication->id);
        $this->assertEquals($communication['subject'], $dbCommunication->subject);
        $this->assertEquals($communication['is_seen'], $dbCommunication->is_seen);
        $this->assertTrue($this->user->is($dbCommunication->getUpdatedBy()));
    }

    public function test_update_communication_maximum(): void
    {
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
        $response->assertStatus(200);

        $responseCommunication = json_decode($response->getContent())->data;
        $dbCommunication = Communication::query()
            ->whereKey($responseCommunication->id)
            ->first();

        $this->assertNotEmpty($dbCommunication);
        $this->assertEquals($communication['subject'], $dbCommunication->subject);
        $this->assertEquals($communication['text_body'], $dbCommunication->text_body);
        $this->assertEquals($communication['html_body'], $dbCommunication->html_body);
        $this->assertEquals($communication['from'], $dbCommunication->from);
        $this->assertEquals($communication['to'], $dbCommunication->to);
        $this->assertEquals($communication['is_seen'], $dbCommunication->is_seen);
    }
}
