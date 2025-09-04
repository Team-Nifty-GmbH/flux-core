<?php

use Carbon\Carbon;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Permission;
use FluxErp\Models\PriceList;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

uses(Illuminate\Foundation\Testing\WithFaker::class);

beforeEach(function (): void {
    $dbClients = Client::factory()->count(2)->create();

    $this->paymentTypes = PaymentType::factory()->count(3)->create([
        'is_active' => true,
        'is_sales' => true,
    ]);
    $dbClients[0]->paymentTypes()->attach([$this->paymentTypes[0]->id, $this->paymentTypes[1]->id]);
    $dbClients[1]->paymentTypes()->attach($this->paymentTypes[2]->id);

    $this->contacts = Contact::factory()->count(2)->create([
        'client_id' => $dbClients[0]->id,
        'payment_type_id' => $this->paymentTypes[0]->id,
    ]);
    $this->contacts[] = Contact::factory()->create([
        'client_id' => $dbClients[1]->id,
        'payment_type_id' => $this->paymentTypes[1]->id,
    ]);

    $this->user->clients()->attach($dbClients->pluck('id')->toArray());

    $this->permissions = [
        'show' => Permission::findOrCreate('api.contacts.{id}.get'),
        'index' => Permission::findOrCreate('api.contacts.get'),
        'create' => Permission::findOrCreate('api.contacts.post'),
        'update' => Permission::findOrCreate('api.contacts.put'),
        'delete' => Permission::findOrCreate('api.contacts.{id}.delete'),
    ];
});

test('create contact', function (): void {
    $contact = [
        'client_id' => $this->contacts[0]->client_id,
        'customer_number' => 'Not Existing Customer Number' . Str::random(),
        'contact_id' => $this->contacts[0]->id,
        'iban' => $this->faker->iban(),
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/contacts', $contact);
    $response->assertCreated();

    $responseContact = json_decode($response->getContent())->data;
    $dbContact = Contact::query()
        ->whereKey($responseContact->id)
        ->first();

    expect($dbContact)->not->toBeEmpty();
    expect($dbContact->client_id)->toEqual($contact['client_id']);
    expect($dbContact->customer_number)->toEqual($contact['customer_number']);
    expect($dbContact->payment_type_id)->toEqual(PaymentType::default()?->id);
    expect($dbContact->price_list_id)->toEqual(PriceList::default()?->id);
    expect($dbContact->creditor_number)->not->toBeNull();
    expect($dbContact->payment_target_days)->toBeNull();
    expect($dbContact->payment_reminder_days_1)->toBeNull();
    expect($dbContact->payment_reminder_days_2)->toBeNull();
    expect($dbContact->payment_reminder_days_3)->toBeNull();
    expect($dbContact->discount_days)->toBeNull();
    expect($dbContact->discount_percent)->toBeNull();
    expect($dbContact->credit_line)->toBeNull();
    expect($dbContact->has_sensitive_reminder)->toBeFalse();
    expect($dbContact->has_delivery_lock)->toBeFalse();
    expect($this->user->is($dbContact->getCreatedBy()))->toBeTrue();
    expect($this->user->is($dbContact->getUpdatedBy()))->toBeTrue();
});

test('create contact maximum', function (): void {
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
    $response->assertCreated();

    $responseContact = json_decode($response->getContent())->data;
    $dbContact = Contact::query()
        ->whereKey($responseContact->id)
        ->first();

    expect($dbContact)->not->toBeEmpty();
    expect($dbContact->client_id)->toEqual($contact['client_id']);
    expect($dbContact->payment_type_id)->toEqual($contact['payment_type_id']);
    expect($dbContact->price_list_id)->toEqual(PriceList::default()->getKey());
    expect($dbContact->customer_number)->toEqual($contact['customer_number']);
    expect($dbContact->creditor_number)->toEqual($contact['creditor_number']);
    expect($dbContact->payment_target_days)->toEqual($contact['payment_target_days']);
    expect($dbContact->payment_reminder_days_1)->toEqual($contact['payment_reminder_days_1']);
    expect($dbContact->payment_reminder_days_2)->toEqual($contact['payment_reminder_days_2']);
    expect($dbContact->payment_reminder_days_3)->toEqual($contact['payment_reminder_days_3']);
    expect($dbContact->discount_days)->toEqual($contact['discount_days']);
    expect($dbContact->discount_percent)->toEqual($contact['discount_percent']);
    expect($dbContact->credit_line)->toEqual($contact['credit_line']);
    expect($dbContact->has_sensitive_reminder)->toEqual($contact['has_sensitive_reminder']);
    expect($dbContact->has_delivery_lock)->toEqual($contact['has_delivery_lock']);
    expect($this->user->is($dbContact->getCreatedBy()))->toBeTrue();
    expect($this->user->is($dbContact->getUpdatedBy()))->toBeTrue();
});

test('create contact validation fails', function (): void {
    $contact = [
        'client_id' => $this->contacts[0]->client_id,
        'customer_number' => $this->contacts[0]->customer_number,
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/contacts', $contact);
    $response->assertUnprocessable();
});

test('delete contact', function (): void {
    AdditionalColumn::factory()->create([
        'name' => Str::random(),
        'model_type' => Contact::class,
    ]);

    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/contacts/' . $this->contacts[2]->id);
    $response->assertNoContent();

    $contact = $this->contacts[2]->fresh();
    expect($contact->deleted_at)->not->toBeNull();
    expect($this->user->is($contact->getDeletedBy()))->toBeTrue();
});

test('delete contact contact not found', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/contacts/' . ++$this->contacts[2]->id);
    $response->assertNotFound();
});

test('get contact', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/contacts/' . $this->contacts[0]->id);
    $response->assertOk();

    $json = json_decode($response->getContent());
    $jsonContact = $json->data;

    // Check if controller returns the test contact.
    expect($jsonContact)->not->toBeEmpty();
    expect($jsonContact->id)->toEqual($this->contacts[0]->id);
    expect($jsonContact->payment_type_id)->toEqual($this->contacts[0]->payment_type_id);
    expect($jsonContact->price_list_id)->toEqual($this->contacts[0]->price_list_id);
    expect($jsonContact->client_id)->toEqual($this->contacts[0]->client_id);
    expect($jsonContact->customer_number)->toEqual($this->contacts[0]->customer_number);
    expect($jsonContact->creditor_number)->toEqual($this->contacts[0]->creditor_number);
    expect($jsonContact->payment_target_days)->toEqual($this->contacts[0]->payment_target_days);
    expect($jsonContact->payment_reminder_days_1)->toEqual($this->contacts[0]->payment_reminder_days_1);
    expect($jsonContact->payment_reminder_days_2)->toEqual($this->contacts[0]->payment_reminder_days_2);
    expect($jsonContact->payment_reminder_days_3)->toEqual($this->contacts[0]->payment_reminder_days_3);
    expect($jsonContact->discount_days)->toEqual($this->contacts[0]->discount_days);
    expect($jsonContact->discount_percent)->toEqual($this->contacts[0]->discount_percent);
    expect($jsonContact->credit_line)->toEqual($this->contacts[0]->credit_line);
    expect($jsonContact->has_sensitive_reminder)->toEqual($this->contacts[0]->has_sensitive_reminder);
    expect($jsonContact->has_delivery_lock)->toEqual($this->contacts[0]->has_delivery_lock);
    expect(Carbon::parse($jsonContact->created_at))->toEqual(Carbon::parse($this->contacts[0]->created_at));
    expect(Carbon::parse($jsonContact->updated_at))->toEqual(Carbon::parse($this->contacts[0]->updated_at));
});

test('get contact contact not found', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/contacts/' . ++$this->contacts[2]->id);
    $response->assertNotFound();
});

test('get contacts', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/contacts');
    $response->assertOk();

    $json = json_decode($response->getContent());
    $jsonContacts = collect($json->data->data);

    // Check the amount of test contacts.
    expect(count($jsonContacts))->toBeGreaterThanOrEqual(2);

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
});

test('update contact', function (): void {
    $contact = [
        'id' => $this->contacts[0]->id,
        'customer_number' => uniqid(),
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/contacts', $contact);
    $response->assertOk();

    $responseContact = json_decode($response->getContent())->data;
    $dbContact = Contact::query()
        ->whereKey($responseContact->id)
        ->first();

    expect($dbContact)->not->toBeEmpty();
    expect($dbContact->id)->toEqual($contact['id']);
    expect($dbContact->customer_number)->toEqual($contact['customer_number']);
    expect($this->user->is($dbContact->getUpdatedBy()))->toBeTrue();
});

test('update contact customer number already exists', function (): void {
    $contact = [
        'id' => $this->contacts[0]->id,
        'customer_number' => $this->contacts[1]->customer_number,
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/contacts', $contact);
    $response->assertUnprocessable();

    $responseContact = json_decode($response->getContent());
    expect(property_exists($responseContact->errors, 'customer_number'))->toBeTrue();
});

test('update contact maximum', function (): void {
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
    $response->assertOk();

    $responseContact = json_decode($response->getContent())->data;
    $dbContact = Contact::query()
        ->whereKey($responseContact->id)
        ->first();

    expect($dbContact)->not->toBeEmpty();
    expect($dbContact->id)->toEqual($contact['id']);
    expect($dbContact->client_id)->toEqual($contact['client_id']);
    expect($dbContact->payment_type_id)->toEqual($contact['payment_type_id']);
    expect($dbContact->price_list_id)->toEqual($contact['price_list_id']);
    expect($dbContact->customer_number)->toEqual($contact['customer_number']);
    expect($dbContact->creditor_number)->toEqual($contact['creditor_number']);
    expect($dbContact->payment_target_days)->toEqual($contact['payment_target_days']);
    expect($dbContact->payment_reminder_days_1)->toEqual($contact['payment_reminder_days_1']);
    expect($dbContact->payment_reminder_days_2)->toEqual($contact['payment_reminder_days_2']);
    expect($dbContact->payment_reminder_days_3)->toEqual($contact['payment_reminder_days_3']);
    expect($dbContact->discount_days)->toEqual($contact['discount_days']);
    expect($dbContact->discount_percent)->toEqual($contact['discount_percent']);
    expect($dbContact->credit_line)->toEqual($contact['credit_line']);
    expect($dbContact->has_sensitive_reminder)->toEqual($contact['has_sensitive_reminder']);
    expect($dbContact->has_delivery_lock)->toEqual($contact['has_delivery_lock']);
    expect($this->user->is($dbContact->getUpdatedBy()))->toBeTrue();
});

test('update contact multi status client payment type not exists', function (): void {
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
    $response->assertUnprocessable();

    $responses = json_decode($response->getContent())->data->items;
    expect($responses[0]->id)->toEqual($contacts[0]['id']);
    expect($responses[0]->status)->toEqual(422);
    expect(property_exists($responses[0]->errors, 'payment_type_id'))->toBeTrue();
    expect($responses[1]->id)->toEqual($contacts[1]['id']);
    expect($responses[1]->status)->toEqual(422);
    expect(property_exists($responses[1]->errors, 'payment_type_id'))->toBeTrue();
    expect($responses[2]->id)->toEqual($contacts[2]['id']);
    expect($responses[2]->status)->toEqual(422);
    expect(property_exists($responses[2]->errors, 'payment_type_id'))->toBeTrue();
});

test('update contact validation fails', function (): void {
    $contact = [
        'customer_number' => $this->contacts[1]->customer_number,
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/contacts', $contact);
    $response->assertUnprocessable();

    $responseContact = json_decode($response->getContent());
    expect($responseContact->status)->toEqual(422);
});
