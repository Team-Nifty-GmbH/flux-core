<?php

namespace FluxErp\Tests\Feature\Api;

use Carbon\Carbon;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;

class ContactTest extends BaseSetup
{
    use DatabaseTransactions, WithFaker;

    private Collection $paymentTypes;

    private Collection $contacts;

    private array $permissions;

    protected function setUp(): void
    {
        parent::setUp();

        $dbClients = Client::factory()->count(2)->create();

        $this->paymentTypes = PaymentType::factory()->count(2)->create([
            'client_id' => $dbClients[0]->id,
        ]);
        $this->paymentTypes[] = PaymentType::factory()->create([
            'client_id' => $dbClients[1]->id,
        ]);

        $this->contacts = Contact::factory()->count(2)->create([
            'client_id' => $dbClients[0]->id,
            'payment_type_id' => $this->paymentTypes[0]->id,
        ]);
        $this->contacts[] = Contact::factory()->create([
            'client_id' => $dbClients[1]->id,
            'payment_type_id' => $this->paymentTypes[1]->id,
        ]);

        $this->permissions = [
            'show' => Permission::findOrCreate('api.contacts.{id}.get'),
            'index' => Permission::findOrCreate('api.contacts.get'),
            'create' => Permission::findOrCreate('api.contacts.post'),
            'update' => Permission::findOrCreate('api.contacts.put'),
            'delete' => Permission::findOrCreate('api.contacts.{id}.delete'),
        ];

        $this->app->make(PermissionRegistrar::class)->registerPermissions();
    }

    public function test_get_contact()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/contacts/' . $this->contacts[0]->id);
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $jsonContact = $json->data;

        // Check if controller returns the test contact.
        $this->assertNotEmpty($jsonContact);
        $this->assertEquals($this->contacts[0]->id, $jsonContact->id);
        $this->assertEquals($this->contacts[0]->payment_type_id, $jsonContact->payment_type_id);
        $this->assertEquals($this->contacts[0]->price_list_id, $jsonContact->price_list_id);
        $this->assertEquals($this->contacts[0]->client_id, $jsonContact->client_id);
        $this->assertEquals($this->contacts[0]->customer_number, $jsonContact->customer_number);
        $this->assertEquals($this->contacts[0]->creditor_number, $jsonContact->creditor_number);
        $this->assertEquals($this->contacts[0]->payment_target_days, $jsonContact->payment_target_days);
        $this->assertEquals($this->contacts[0]->payment_reminder_days_1, $jsonContact->payment_reminder_days_1);
        $this->assertEquals($this->contacts[0]->payment_reminder_days_2, $jsonContact->payment_reminder_days_2);
        $this->assertEquals($this->contacts[0]->payment_reminder_days_3, $jsonContact->payment_reminder_days_3);
        $this->assertEquals($this->contacts[0]->discount_days, $jsonContact->discount_days);
        $this->assertEquals($this->contacts[0]->discount_percent, $jsonContact->discount_percent);
        $this->assertEquals($this->contacts[0]->credit_line, $jsonContact->credit_line);
        $this->assertEquals($this->contacts[0]->has_sensitive_reminder, $jsonContact->has_sensitive_reminder);
        $this->assertEquals($this->contacts[0]->has_delivery_lock, $jsonContact->has_delivery_lock);
        $this->assertEquals(Carbon::parse($this->contacts[0]->created_at),
            Carbon::parse($jsonContact->created_at));
        $this->assertEquals(Carbon::parse($this->contacts[0]->updated_at),
            Carbon::parse($jsonContact->updated_at));
    }

    public function test_get_contact_contact_not_found()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/contacts/' . ++$this->contacts[2]->id);
        $response->assertStatus(404);
    }

    public function test_get_contacts()
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/contacts');
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $jsonContacts = collect($json->data->data);

        // Check the amount of test contacts.
        $this->assertGreaterThanOrEqual(2, count($jsonContacts));

        // Check if controller returns the test contacts.
        foreach ($this->contacts as $contact) {
            $jsonContacts->contains(function ($jsonContact) use ($contact) {
                return $jsonContact->id === $contact->id &&
                    $jsonContact->payment_type_id === $contact->payment_type_id &&
                    $jsonContact->price_list_id === $contact->price_list_id &&
                    $jsonContact->client_id === $contact->client_id &&
                    $jsonContact->customer_number === $contact->customer_number &&
                    $jsonContact->creditor_number === $contact->creditor_number &&
                    $jsonContact->payment_target_days === $contact->payment_target_days &&
                    $jsonContact->payment_reminder_days_1 === $contact->payment_reminder_days_1 &&
                    $jsonContact->payment_reminder_days_2 === $contact->payment_reminder_days_2 &&
                    $jsonContact->payment_reminder_days_3 === $contact->payment_reminder_days_3 &&
                    $jsonContact->discount_days === $contact->discount_days &&
                    $jsonContact->discount_percent === $contact->discount_percent &&
                    $jsonContact->credit_line === $contact->credit_line &&
                    $jsonContact->has_sensitive_reminder === $contact->has_sensitive_reminder &&
                    $jsonContact->has_delivery_lock === $contact->has_delivery_lock &&
                    Carbon::parse($jsonContact->created_at) === Carbon::parse($contact->created_at) &&
                    Carbon::parse($jsonContact->updated_at) === Carbon::parse($contact->updated_at);
            });
        }
    }

    public function test_create_contact()
    {
        $contact = [
            'client_id' => $this->contacts[0]->client_id,
            'customer_number' => 'Not Existing Customer Number' . Str::random(),
            'contact_id' => $this->contacts[0]->id,
            'iban' => $this->faker->iban(),
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/contacts', $contact);
        $response->assertStatus(201);

        $responseContact = json_decode($response->getContent())->data;
        $dbContact = Contact::query()
            ->whereKey($responseContact->id)
            ->first();

        $this->assertNotEmpty($dbContact);
        $this->assertEquals($contact['client_id'], $dbContact->client_id);
        $this->assertEquals($contact['customer_number'], $dbContact->customer_number);
        $this->assertNull($dbContact->payment_type_id);
        $this->assertNull($dbContact->price_list_id);
        $this->assertNotNull($dbContact->creditor_number);
        $this->assertNull($dbContact->payment_target_days);
        $this->assertNull($dbContact->payment_reminder_days_1);
        $this->assertNull($dbContact->payment_reminder_days_2);
        $this->assertNull($dbContact->payment_reminder_days_3);
        $this->assertNull($dbContact->discount_days);
        $this->assertNull($dbContact->discount_percent);
        $this->assertNull($dbContact->credit_line);
        $this->assertFalse($dbContact->has_sensitive_reminder);
        $this->assertFalse($dbContact->has_delivery_lock);
        $this->assertEquals($this->user->id, $dbContact->created_by->id);
        $this->assertEquals($this->user->id, $dbContact->updated_by->id);
    }

    public function test_create_contact_maximum()
    {
        $contact = [
            'client_id' => $this->contacts[0]->client_id,
            'payment_type_id' => $this->paymentTypes[1]->id,
            'price_list_id' => null,
            'customer_number' => 'Not Existing Customer Number' . Str::random(),
            'creditor_number' => Str::random(),
            'payment_target_days' => rand(1, 1024),
            'payment_reminder_days_1' => rand(1, 1024),
            'payment_reminder_days_2' => rand(1, 1024),
            'payment_reminder_days_3' => rand(1, 1024),
            'discount_days' => rand(0, 1024),
            'discount_percent' => rand(0, 100),
            'credit_line' => rand(0, 8192),
            'has_sensitive_reminder' => true,
            'has_delivery_lock' => true,
            'contact_id' => $this->contacts[0]->id,
            'iban' => $this->faker->iban(),
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/contacts', $contact);
        $response->assertStatus(201);

        $responseContact = json_decode($response->getContent())->data;
        $dbContact = Contact::query()
            ->whereKey($responseContact->id)
            ->first();

        $this->assertNotEmpty($dbContact);
        $this->assertEquals($contact['client_id'], $dbContact->client_id);
        $this->assertEquals($contact['payment_type_id'], $dbContact->payment_type_id);
        $this->assertEquals($contact['price_list_id'], $dbContact->price_list_id);
        $this->assertEquals($contact['customer_number'], $dbContact->customer_number);
        $this->assertEquals($contact['creditor_number'], $dbContact->creditor_number);
        $this->assertEquals($contact['payment_target_days'], $dbContact->payment_target_days);
        $this->assertEquals($contact['payment_reminder_days_1'], $dbContact->payment_reminder_days_1);
        $this->assertEquals($contact['payment_reminder_days_2'], $dbContact->payment_reminder_days_2);
        $this->assertEquals($contact['payment_reminder_days_3'], $dbContact->payment_reminder_days_3);
        $this->assertEquals($contact['discount_days'], $dbContact->discount_days);
        $this->assertEquals($contact['discount_percent'], $dbContact->discount_percent);
        $this->assertEquals($contact['credit_line'], $dbContact->credit_line);
        $this->assertEquals($contact['has_sensitive_reminder'], $dbContact->has_sensitive_reminder);
        $this->assertEquals($contact['has_delivery_lock'], $dbContact->has_delivery_lock);
        $this->assertEquals($this->user->id, $dbContact->created_by->id);
        $this->assertEquals($this->user->id, $dbContact->updated_by->id);
    }

    public function test_create_contact_validation_fails()
    {
        $contact = [
            'client_id' => $this->contacts[0]->client_id,
            'customer_number' => $this->contacts[0]->customer_number,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/contacts', $contact);
        $response->assertStatus(422);
    }

    public function test_update_contact()
    {
        $contact = [
            'id' => $this->contacts[0]->id,
            'customer_number' => uniqid(),
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/contacts', $contact);
        $response->assertStatus(200);

        $responseContact = json_decode($response->getContent())->data;
        $dbContact = Contact::query()
            ->whereKey($responseContact->id)
            ->first();

        $this->assertNotEmpty($dbContact);
        $this->assertEquals($contact['id'], $dbContact->id);
        $this->assertEquals($contact['customer_number'], $dbContact->customer_number);
        $this->assertEquals($this->user->id, $dbContact->updated_by->id);
    }

    public function test_update_contact_maximum()
    {
        $contact = [
            'id' => $this->contacts[0]->id,
            'client_id' => $this->contacts[2]->client_id,
            'payment_type_id' => $this->paymentTypes[2]->id,
            'price_list_id' => null,
            'customer_number' => 'Not Existing Customer Number' . Str::random(),
            'creditor_number' => Str::random(),
            'payment_target_days' => rand(1, 1024),
            'payment_reminder_days_1' => rand(1, 1024),
            'payment_reminder_days_2' => rand(1, 1024),
            'payment_reminder_days_3' => rand(1, 1024),
            'discount_days' => rand(0, 1024),
            'discount_percent' => rand(0, 100),
            'credit_line' => rand(0, 8192),
            'has_sensitive_reminder' => true,
            'has_delivery_lock' => true,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/contacts', $contact);
        $response->assertStatus(200);

        $responseContact = json_decode($response->getContent())->data;
        $dbContact = Contact::query()
            ->whereKey($responseContact->id)
            ->first();

        $this->assertNotEmpty($dbContact);
        $this->assertEquals($contact['id'], $dbContact->id);
        $this->assertEquals($contact['client_id'], $dbContact->client_id);
        $this->assertEquals($contact['payment_type_id'], $dbContact->payment_type_id);
        $this->assertEquals($contact['price_list_id'], $dbContact->price_list_id);
        $this->assertEquals($contact['customer_number'], $dbContact->customer_number);
        $this->assertEquals($contact['creditor_number'], $dbContact->creditor_number);
        $this->assertEquals($contact['payment_target_days'], $dbContact->payment_target_days);
        $this->assertEquals($contact['payment_reminder_days_1'], $dbContact->payment_reminder_days_1);
        $this->assertEquals($contact['payment_reminder_days_2'], $dbContact->payment_reminder_days_2);
        $this->assertEquals($contact['payment_reminder_days_3'], $dbContact->payment_reminder_days_3);
        $this->assertEquals($contact['discount_days'], $dbContact->discount_days);
        $this->assertEquals($contact['discount_percent'], $dbContact->discount_percent);
        $this->assertEquals($contact['credit_line'], $dbContact->credit_line);
        $this->assertEquals($contact['has_sensitive_reminder'], $dbContact->has_sensitive_reminder);
        $this->assertEquals($contact['has_delivery_lock'], $dbContact->has_delivery_lock);
        $this->assertEquals($this->user->id, $dbContact->updated_by->id);
    }

    public function test_update_contact_multi_status_validation_fails()
    {
        $contact = [
            'customer_number' => $this->contacts[1]->customer_number,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/contacts', $contact);
        $response->assertStatus(422);

        $responseContact = json_decode($response->getContent());
        $this->assertNull($responseContact->id);
        $this->assertEquals(422, $responseContact->status);
    }

    public function test_update_contact_multi_status_customer_number_already_exists()
    {
        $contact = [
            'id' => $this->contacts[0]->id,
            'customer_number' => $this->contacts[1]->customer_number,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/contacts', $contact);
        $response->assertStatus(422);

        $responseContact = json_decode($response->getContent());
        $this->assertEquals($contact['id'], $responseContact->id);
        $this->assertTrue(property_exists($responseContact->errors, 'customer_number'));
    }

    public function test_update_contact_multi_status_client_payment_type_not_exists()
    {
        $contacts = [
            [
                'id' => $this->contacts[0]->id,
                'payment_type_id' => $this->paymentTypes[2]->id,
            ],
            [
                'id' => $this->contacts[1]->id,
                'client_id' => $this->contacts[2]->client_id,
            ],
            [
                'id' => $this->contacts[2]->id,
                'client_id' => $this->contacts[0]->client_id,
                'payment_type_id' => $this->paymentTypes[2]->id,
            ],
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/contacts', $contacts);
        $response->assertStatus(422);

        $responses = json_decode($response->getContent())->responses;
        $this->assertEquals($contacts[0]['id'], $responses[0]->id);
        $this->assertEquals(422, $responses[0]->status);
        $this->assertTrue(property_exists($responses[0]->errors, 'payment_type_id'));
        $this->assertEquals($contacts[1]['id'], $responses[1]->id);
        $this->assertEquals(422, $responses[1]->status);
        $this->assertTrue(property_exists($responses[1]->errors, 'payment_type_id'));
        $this->assertEquals($contacts[2]['id'], $responses[2]->id);
        $this->assertEquals(422, $responses[2]->status);
        $this->assertTrue(property_exists($responses[2]->errors, 'payment_type_id'));
    }

    public function test_delete_contact()
    {
        AdditionalColumn::factory()->create([
            'name' => Str::random(),
            'model_type' => Contact::class,
        ]);

        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/contacts/' . $this->contacts[2]->id);
        $response->assertStatus(204);

        $contact = $this->contacts[2]->fresh();
        $this->assertNotNull($contact->deleted_at);
        $this->assertEquals($this->user->id, $contact->deleted_by->id);
    }

    public function test_delete_contact_contact_not_found()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/contacts/' . ++$this->contacts[2]->id);
        $response->assertStatus(404);
    }
}
