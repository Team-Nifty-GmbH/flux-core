<?php

uses(FluxErp\Tests\Feature\BaseSetup::class);
use Carbon\Carbon;
use FluxErp\Models\Address;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\Country;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Permission;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $dbClients = Client::factory()->count(2)->create();

    $this->languages = Language::factory()->count(2)->create();
    $currency = Currency::factory()->create();

    $this->countries = Country::factory()->count(2)->create([
        'language_id' => $this->languages[0]->id,
        'currency_id' => $currency->id,
        'is_default' => false,
    ]);
    $this->countries[] = Country::factory()->create([
        'language_id' => $this->languages[1]->id,
        'currency_id' => $currency->id,
        'is_default' => true,
    ]);

    $paymentTypes = PaymentType::factory()->count(3)->create();
    $dbClients[0]->paymentTypes()->attach([$paymentTypes[0]->id, $paymentTypes[1]->id]);
    $dbClients[1]->paymentTypes()->attach($paymentTypes[2]->id);

    $this->contacts = Contact::factory()->count(2)->create([
        'client_id' => $dbClients[0]->id,
        'payment_type_id' => $paymentTypes[0]->id,
    ]);
    $this->contacts[] = Contact::factory()->create([
        'client_id' => $dbClients[1]->id,
        'payment_type_id' => $paymentTypes[1]->id,
    ]);

    $this->addresses = Address::factory()->count(3)->create([
        'client_id' => $dbClients[0]->id,
        'contact_id' => $this->contacts[0]->id,
        'language_id' => $this->languages[0]->id,
        'country_id' => $this->countries[0]->id,
        'is_main_address' => false,
    ]);
    $this->addresses[] = Address::factory()->create([
        'client_id' => $dbClients[1]->id,
        'contact_id' => $this->contacts[2]->id,
        'language_id' => $this->languages[1]->id,
        'country_id' => $this->countries[2]->id,
        'is_main_address' => true,
    ]);

    $this->user->clients()->attach($dbClients->pluck('id')->toArray());

    $this->permissions = [
        'show' => Permission::findOrCreate('api.addresses.{id}.get'),
        'index' => Permission::findOrCreate('api.addresses.get'),
        'create' => Permission::findOrCreate('api.addresses.post'),
        'update' => Permission::findOrCreate('api.addresses.put'),
        'delete' => Permission::findOrCreate('api.addresses.{id}.delete'),
    ];
});

test('create address', function (): void {
    $address = [
        'client_id' => $this->contacts[2]->client_id,
        'contact_id' => $this->contacts[2]->id,
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->post('/api/addresses', $address);
    $response->assertStatus(201);

    $responseAddress = json_decode($response->getContent())->data;
    $dbAddress = Address::query()
        ->whereKey($responseAddress->id)
        ->first();

    expect($dbAddress)->not->toBeEmpty();
    expect($dbAddress->client_id)->toEqual($address['client_id']);
    expect($dbAddress->contact_id)->toEqual($address['contact_id']);
    expect($dbAddress->language_id)->toBeNull();
    expect($dbAddress->country_id)->toEqual($this->countries[2]->id);
    expect($dbAddress->company)->toBeNull();
    expect($dbAddress->title)->toBeNull();
    expect($dbAddress->salutation)->toBeNull();
    expect($dbAddress->firstname)->toBeNull();
    expect($dbAddress->lastname)->toBeNull();
    expect($dbAddress->addition)->toBeNull();
    expect($dbAddress->mailbox)->toBeNull();
    expect($dbAddress->latitude)->toBeNull();
    expect($dbAddress->longitude)->toBeNull();
    expect($dbAddress->zip)->toBeNull();
    expect($dbAddress->city)->toBeNull();
    expect($dbAddress->street)->toBeNull();
    expect($dbAddress->url)->toBeNull();
    expect($dbAddress->date_of_birth)->toBeNull();
    expect($dbAddress->department)->toBeNull();
    expect($dbAddress->is_main_address)->toBeFalse();
    expect($dbAddress->is_active)->toBeTrue();
    expect($this->user->is($dbAddress->getCreatedBy()))->toBeTrue();
    expect($this->user->is($dbAddress->getUpdatedBy()))->toBeTrue();
});

test('create address maximum', function (): void {
    $address = [
        'client_id' => $this->contacts[1]->client_id,
        'contact_id' => $this->contacts[1]->id,
        'language_id' => $this->languages[0]->id,
        'country_id' => $this->countries[2]->id,
        'company' => Str::random(),
        'title' => Str::random(),
        'salutation' => Str::random(),
        'firstname' => Str::random(),
        'lastname' => Str::random(),
        'addition' => Str::random(),
        'mailbox' => Str::random(),
        'latitude' => 37.88953,
        'longitude' => 41.12802,
        'zip' => 123456,
        'city' => Str::random(),
        'street' => Str::random(),
        'url' => Str::random(),
        'date_of_birth' => date('Y-m-d'),
        'department' => Str::random(),
        'is_main_address' => true,
        'is_active' => false,
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/addresses', $address);
    $response->assertStatus(201);

    $responseAddress = json_decode($response->getContent())->data;
    $dbAddress = Address::query()
        ->whereKey($responseAddress->id)
        ->first();

    expect($dbAddress)->not->toBeEmpty();
    expect($dbAddress->client_id)->toEqual($address['client_id']);
    expect($dbAddress->contact_id)->toEqual($address['contact_id']);
    expect($dbAddress->language_id)->toEqual($address['language_id']);
    expect($dbAddress->country_id)->toEqual($address['country_id']);
    expect($dbAddress->company)->toEqual($address['company']);
    expect($dbAddress->title)->toEqual($address['title']);
    expect($dbAddress->salutation)->toEqual($address['salutation']);
    expect($dbAddress->firstname)->toEqual($address['firstname']);
    expect($dbAddress->lastname)->toEqual($address['lastname']);
    expect($dbAddress->addition)->toEqual($address['addition']);
    expect($dbAddress->mailbox)->toEqual($address['mailbox']);
    expect($dbAddress->latitude)->toEqual($address['latitude']);
    expect($dbAddress->longitude)->toEqual($address['longitude']);
    expect($dbAddress->zip)->toEqual($address['zip']);
    expect($dbAddress->street)->toEqual($address['street']);
    expect($dbAddress->city)->toEqual($address['city']);
    expect($dbAddress->url)->toEqual($address['url']);
    expect($dbAddress->date_of_birth->toDateString())->toEqual($address['date_of_birth']);
    expect($dbAddress->department)->toEqual($address['department']);
    expect($dbAddress->is_main_address)->toEqual($address['is_main_address']);
    expect($dbAddress->is_active)->toEqual($address['is_active']);
    expect($this->user->is($dbAddress->getCreatedBy()))->toBeTrue();
    expect($this->user->is($dbAddress->getUpdatedBy()))->toBeTrue();
});

test('create address validation fails', function (): void {
    $address = [
        'client_id' => $this->addresses[2]->client_id,
        'contact_id' => ++$this->addresses[3]->contact_id,
        'zip' => -123456,
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/addresses', $address);
    $response->assertStatus(422);

    $response->assertJsonValidationErrors([
        'contact_id',
        'zip',
    ]);
});

test('delete address', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/addresses/' . $this->addresses[2]->id);
    $response->assertStatus(204);

    $address = $this->addresses[2]->fresh();
    expect($address->deleted_at)->not->toBeNull();
    expect($this->user->is($address->getDeletedBy()))->toBeTrue();
});

test('delete address address not found', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/addresses/' . ++$this->addresses[3]->id);
    $response->assertStatus(404);
});

test('get address', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/addresses/' . $this->addresses[0]->id);
    $response->assertStatus(200);

    $json = json_decode($response->getContent());
    $jsonAddress = $json->data;

    // Check if controller returns the test address.
    expect($jsonAddress)->not->toBeEmpty();
    expect($jsonAddress->id)->toEqual($this->addresses[0]->id);
    expect($jsonAddress->language_id)->toEqual($this->addresses[0]->language_id);
    expect($jsonAddress->country_id)->toEqual($this->addresses[0]->country_id);
    expect($jsonAddress->contact_id)->toEqual($this->addresses[0]->contact_id);
    expect($jsonAddress->company)->toEqual($this->addresses[0]->company);
    expect($jsonAddress->title)->toEqual($this->addresses[0]->title);
    expect($jsonAddress->salutation)->toEqual($this->addresses[0]->salutation);
    expect($jsonAddress->firstname)->toEqual($this->addresses[0]->firstname);
    expect($jsonAddress->lastname)->toEqual($this->addresses[0]->lastname);
    expect($jsonAddress->addition)->toEqual($this->addresses[0]->addition);
    expect($jsonAddress->mailbox)->toEqual($this->addresses[0]->mailbox);
    expect($jsonAddress->latitude)->toEqual($this->addresses[0]->latitude);
    expect($jsonAddress->longitude)->toEqual($this->addresses[0]->longitude);
    expect($jsonAddress->zip)->toEqual($this->addresses[0]->zip);
    expect($jsonAddress->city)->toEqual($this->addresses[0]->city);
    expect($jsonAddress->street)->toEqual($this->addresses[0]->street);
    expect($jsonAddress->url)->toEqual($this->addresses[0]->url);
    expect($jsonAddress->date_of_birth ? Carbon::parse($jsonAddress->date_of_birth)->toDateString() : null)->toEqual($this->addresses[0]->date_of_birth?->toDateString());
    expect($jsonAddress->department)->toEqual($this->addresses[0]->department);
    expect($jsonAddress->is_main_address)->toEqual($this->addresses[0]->is_main_address);
    expect($jsonAddress->is_active)->toEqual($this->addresses[0]->is_active);
    expect(Carbon::parse($jsonAddress->created_at))->toEqual(Carbon::parse($this->addresses[0]->created_at));
    expect(Carbon::parse($jsonAddress->updated_at))->toEqual(Carbon::parse($this->addresses[0]->updated_at));
});

test('get address address not found', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/addresses/' . ++$this->addresses[3]->id);
    $response->assertStatus(404);
});

test('get addresses', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/addresses');
    $response->assertStatus(200);

    $json = json_decode($response->getContent());
    $jsonAddresses = collect($json->data->data);

    // Check the amount of test addresses.
    expect(count($jsonAddresses))->toBeGreaterThanOrEqual(2);

    // Check if controller returns the test addresses.
    foreach ($this->addresses as $address) {
        $jsonAddresses->contains(function ($jsonAddress) use ($address) {
            return $jsonAddress->id === $address->id &&
                $jsonAddress->language_id === $address->language_id &&
                $jsonAddress->country_id === $address->country_id &&
                $jsonAddress->contact_id === $address->contact_id &&
                $jsonAddress->company === $address->company &&
                $jsonAddress->title === $address->title &&
                $jsonAddress->salutation === $address->salutation &&
                $jsonAddress->firstname === $address->firstname &&
                $jsonAddress->lastname === $address->lastname &&
                $jsonAddress->addition === $address->addition &&
                $jsonAddress->mailbox === $address->mailbox &&
                $jsonAddress->latitude === $address->latitude &&
                $jsonAddress->longitude === $address->longitude &&
                $jsonAddress->zip === $address->zip &&
                $jsonAddress->city === $address->city &&
                $jsonAddress->street === $address->street &&
                $jsonAddress->url === $address->url &&
                $jsonAddress->date_of_birth === $address->date_of_birth &&
                $jsonAddress->department === $address->department &&
                $jsonAddress->is_main_address === $address->is_main_address &&
                $jsonAddress->is_active === $address->is_active &&
                Carbon::parse($jsonAddress->created_at) === Carbon::parse($address->created_at) &&
                Carbon::parse($jsonAddress->updated_at) === Carbon::parse($address->updated_at);
        });
    }
});

test('update address', function (): void {
    $address = [
        'id' => $this->addresses[0]->id,
        'firstname' => uniqid(),
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/addresses', $address);
    $response->assertStatus(200);

    $responseAddress = json_decode($response->getContent())->data;
    $dbAddress = Address::query()
        ->whereKey($responseAddress->id)
        ->first()
        ->append(['created_by', 'updated_by']);

    expect($dbAddress)->not->toBeEmpty();
    expect($dbAddress->id)->toEqual($address['id']);
    expect($dbAddress->firstname)->toEqual($address['firstname']);
    expect($this->user->is($dbAddress->getUpdatedBy()))->toBeTrue();
});

test('update address delete tokens', function (): void {
    $this->addresses[3]->can_login = true;
    $this->addresses[3]->save();

    $address = [
        'id' => $this->addresses[3]->id,
        'can_login' => false,
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/addresses', $address);
    $response->assertStatus(200);

    $responseAddress = json_decode($response->getContent())->data;

    $dbAddress = Address::query()
        ->whereKey($responseAddress->id)
        ->first();

    expect($dbAddress)->not->toBeEmpty();
    expect($dbAddress->id)->toEqual($address['id']);
    expect($dbAddress->can_login)->toBeFalse();
    expect($dbAddress->tokens()->exists())->toBeFalse();
});

test('update address maximum', function (): void {
    $address = [
        'id' => $this->addresses[0]->id,
        'language_id' => $this->languages[1]->id,
        'country_id' => $this->countries[1]->id,
        'contact_id' => $this->contacts[0]->id,
        'company' => Str::random(),
        'title' => Str::random(),
        'salutation' => Str::random(),
        'firstname' => Str::random(),
        'lastname' => Str::random(),
        'addition' => Str::random(),
        'mailbox' => Str::random(),
        'latitude' => 90,
        'longitude' => -180,
        'zip' => Str::random(),
        'street' => Str::random(),
        'city' => Str::random(),
        'url' => Str::random(),
        'date_of_birth' => date('Y-m-d'),
        'department' => Str::random(),
        'is_main_address' => true,
        'is_active' => true,
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/addresses', $address);
    $response->assertStatus(200);

    $responseAddress = json_decode($response->getContent())->data;
    $dbAddress = Address::query()
        ->whereKey($responseAddress->id)
        ->first();

    expect($dbAddress)->not->toBeEmpty();
    expect($dbAddress->id)->toEqual($address['id']);
    expect($dbAddress->language_id)->toEqual($address['language_id']);
    expect($dbAddress->country_id)->toEqual($address['country_id']);
    expect($dbAddress->contact_id)->toEqual($address['contact_id']);
    expect($dbAddress->company)->toEqual($address['company']);
    expect($dbAddress->title)->toEqual($address['title']);
    expect($dbAddress->salutation)->toEqual($address['salutation']);
    expect($dbAddress->firstname)->toEqual($address['firstname']);
    expect($dbAddress->lastname)->toEqual($address['lastname']);
    expect($dbAddress->addition)->toEqual($address['addition']);
    expect($dbAddress->mailbox)->toEqual($address['mailbox']);
    expect($dbAddress->latitude)->toEqual($address['latitude']);
    expect($dbAddress->longitude)->toEqual($address['longitude']);
    expect($dbAddress->zip)->toEqual($address['zip']);
    expect($dbAddress->city)->toEqual($address['city']);
    expect($dbAddress->street)->toEqual($address['street']);
    expect($dbAddress->url)->toEqual($address['url']);
    expect($dbAddress->date_of_birth->toDateString())->toEqual($address['date_of_birth']);
    expect($dbAddress->department)->toEqual($address['department']);
    expect($dbAddress->is_main_address)->toEqual($address['is_main_address']);
    expect($dbAddress->is_active)->toEqual($address['is_active']);
    expect($this->user->is($dbAddress->getUpdatedBy()))->toBeTrue();
});

test('update address multi status validation fails', function (): void {
    $address = [
        [
            'id' => $this->addresses[0]->id,
            'latitude' => 90.00001,
        ],
        [
            'id' => $this->addresses[1]->id,
            'latitude' => -90.00001,
        ],
        [
            'id' => $this->addresses[2]->id,
            'longitude' => 180.00001,
        ],
        [
            'id' => $this->addresses[0]->id,
            'longitude' => -180.00001,
        ],
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/addresses', $address);
    $response->assertStatus(422);

    $responses = json_decode($response->getContent())->data->items;
    expect($responses[0]->id)->toEqual($address[0]['id']);
    expect($responses[0]->status)->toEqual(422);
    expect($responses[1]->id)->toEqual($address[1]['id']);
    expect($responses[1]->status)->toEqual(422);
    expect($responses[2]->id)->toEqual($address[2]['id']);
    expect($responses[2]->status)->toEqual(422);
    expect($responses[3]->id)->toEqual($address[3]['id']);
    expect($responses[3]->status)->toEqual(422);
});
