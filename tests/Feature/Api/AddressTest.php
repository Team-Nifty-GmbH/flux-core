<?php

namespace FluxErp\Tests\Feature\Api;

use Carbon\Carbon;
use FluxErp\Models\Address;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\Country;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

class AddressTest extends BaseSetup
{
    use DatabaseTransactions;

    private Collection $contacts;

    private Collection $countries;

    private Collection $languages;

    private Collection $addresses;

    private array $permissions;

    protected function setUp(): void
    {
        parent::setUp();

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

        $paymentTypes = PaymentType::factory()->count(2)->create([
            'client_id' => $dbClients[0]->id,
        ]);
        $paymentTypes[] = PaymentType::factory()->create([
            'client_id' => $dbClients[1]->id,
        ]);

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
    }

    public function test_get_address()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/addresses/' . $this->addresses[0]->id);
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $jsonAddress = $json->data;

        // Check if controller returns the test address.
        $this->assertNotEmpty($jsonAddress);
        $this->assertEquals($this->addresses[0]->id, $jsonAddress->id);
        $this->assertEquals($this->addresses[0]->language_id, $jsonAddress->language_id);
        $this->assertEquals($this->addresses[0]->country_id, $jsonAddress->country_id);
        $this->assertEquals($this->addresses[0]->contact_id, $jsonAddress->contact_id);
        $this->assertEquals($this->addresses[0]->company, $jsonAddress->company);
        $this->assertEquals($this->addresses[0]->title, $jsonAddress->title);
        $this->assertEquals($this->addresses[0]->salutation, $jsonAddress->salutation);
        $this->assertEquals($this->addresses[0]->firstname, $jsonAddress->firstname);
        $this->assertEquals($this->addresses[0]->lastname, $jsonAddress->lastname);
        $this->assertEquals($this->addresses[0]->addition, $jsonAddress->addition);
        $this->assertEquals($this->addresses[0]->mailbox, $jsonAddress->mailbox);
        $this->assertEquals($this->addresses[0]->latitude, $jsonAddress->latitude);
        $this->assertEquals($this->addresses[0]->longitude, $jsonAddress->longitude);
        $this->assertEquals($this->addresses[0]->zip, $jsonAddress->zip);
        $this->assertEquals($this->addresses[0]->city, $jsonAddress->city);
        $this->assertEquals($this->addresses[0]->street, $jsonAddress->street);
        $this->assertEquals($this->addresses[0]->url, $jsonAddress->url);
        $this->assertEquals(
            $this->addresses[0]->date_of_birth?->toDateString(),
            $jsonAddress->date_of_birth ? Carbon::parse($jsonAddress->date_of_birth)->toDateString() : null
        );
        $this->assertEquals($this->addresses[0]->department, $jsonAddress->department);
        $this->assertEquals($this->addresses[0]->is_main_address, $jsonAddress->is_main_address);
        $this->assertEquals($this->addresses[0]->is_active, $jsonAddress->is_active);
        $this->assertEquals(Carbon::parse($this->addresses[0]->created_at),
            Carbon::parse($jsonAddress->created_at));
        $this->assertEquals(Carbon::parse($this->addresses[0]->updated_at),
            Carbon::parse($jsonAddress->updated_at));
    }

    public function test_get_address_address_not_found()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/addresses/' . ++$this->addresses[3]->id);
        $response->assertStatus(404);
    }

    public function test_get_addresses()
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/addresses');
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $jsonAddresses = collect($json->data->data);

        // Check the amount of test addresses.
        $this->assertGreaterThanOrEqual(2, count($jsonAddresses));

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
    }

    public function test_create_address()
    {
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

        $this->assertNotEmpty($dbAddress);
        $this->assertEquals($address['client_id'], $dbAddress->client_id);
        $this->assertEquals($address['contact_id'], $dbAddress->contact_id);
        $this->assertNull($dbAddress->language_id);
        $this->assertEquals($this->countries[2]->id, $dbAddress->country_id);
        $this->assertNull($dbAddress->company);
        $this->assertNull($dbAddress->title);
        $this->assertNull($dbAddress->salutation);
        $this->assertNull($dbAddress->firstname);
        $this->assertNull($dbAddress->lastname);
        $this->assertNull($dbAddress->addition);
        $this->assertNull($dbAddress->mailbox);
        $this->assertNull($dbAddress->latitude);
        $this->assertNull($dbAddress->longitude);
        $this->assertNull($dbAddress->zip);
        $this->assertNull($dbAddress->city);
        $this->assertNull($dbAddress->street);
        $this->assertNull($dbAddress->url);
        $this->assertNull($dbAddress->date_of_birth);
        $this->assertNull($dbAddress->department);
        $this->assertFalse($dbAddress->is_main_address);
        $this->assertTrue($dbAddress->is_active);
        $this->assertTrue($this->user->is($dbAddress->getCreatedBy()));
        $this->assertTrue($this->user->is($dbAddress->getUpdatedBy()));
    }

    public function test_create_address_maximum()
    {
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

        $this->assertNotEmpty($dbAddress);
        $this->assertEquals($address['client_id'], $dbAddress->client_id);
        $this->assertEquals($address['contact_id'], $dbAddress->contact_id);
        $this->assertEquals($address['language_id'], $dbAddress->language_id);
        $this->assertEquals($address['country_id'], $dbAddress->country_id);
        $this->assertEquals($address['company'], $dbAddress->company);
        $this->assertEquals($address['title'], $dbAddress->title);
        $this->assertEquals($address['salutation'], $dbAddress->salutation);
        $this->assertEquals($address['firstname'], $dbAddress->firstname);
        $this->assertEquals($address['lastname'], $dbAddress->lastname);
        $this->assertEquals($address['addition'], $dbAddress->addition);
        $this->assertEquals($address['mailbox'], $dbAddress->mailbox);
        $this->assertEquals($address['latitude'], $dbAddress->latitude);
        $this->assertEquals($address['longitude'], $dbAddress->longitude);
        $this->assertEquals($address['zip'], $dbAddress->zip);
        $this->assertEquals($address['street'], $dbAddress->street);
        $this->assertEquals($address['city'], $dbAddress->city);
        $this->assertEquals($address['url'], $dbAddress->url);
        $this->assertEquals($address['date_of_birth'], $dbAddress->date_of_birth->toDateString());
        $this->assertEquals($address['department'], $dbAddress->department);
        $this->assertEquals($address['is_main_address'], $dbAddress->is_main_address);
        $this->assertEquals($address['is_active'], $dbAddress->is_active);
        $this->assertTrue($this->user->is($dbAddress->getCreatedBy()));
        $this->assertTrue($this->user->is($dbAddress->getUpdatedBy()));
    }

    public function test_create_address_validation_fails()
    {
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
    }

    public function test_update_address()
    {
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

        $this->assertNotEmpty($dbAddress);
        $this->assertEquals($address['id'], $dbAddress->id);
        $this->assertEquals($address['firstname'], $dbAddress->firstname);
        $this->assertTrue($this->user->is($dbAddress->getUpdatedBy()));
    }

    public function test_update_address_maximum()
    {
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

        $this->assertNotEmpty($dbAddress);
        $this->assertEquals($address['id'], $dbAddress->id);
        $this->assertEquals($address['language_id'], $dbAddress->language_id);
        $this->assertEquals($address['country_id'], $dbAddress->country_id);
        $this->assertEquals($address['contact_id'], $dbAddress->contact_id);
        $this->assertEquals($address['company'], $dbAddress->company);
        $this->assertEquals($address['title'], $dbAddress->title);
        $this->assertEquals($address['salutation'], $dbAddress->salutation);
        $this->assertEquals($address['firstname'], $dbAddress->firstname);
        $this->assertEquals($address['lastname'], $dbAddress->lastname);
        $this->assertEquals($address['addition'], $dbAddress->addition);
        $this->assertEquals($address['mailbox'], $dbAddress->mailbox);
        $this->assertEquals($address['latitude'], $dbAddress->latitude);
        $this->assertEquals($address['longitude'], $dbAddress->longitude);
        $this->assertEquals($address['zip'], $dbAddress->zip);
        $this->assertEquals($address['city'], $dbAddress->city);
        $this->assertEquals($address['street'], $dbAddress->street);
        $this->assertEquals($address['url'], $dbAddress->url);
        $this->assertEquals($address['date_of_birth'], $dbAddress->date_of_birth->toDateString());
        $this->assertEquals($address['department'], $dbAddress->department);
        $this->assertEquals($address['is_main_address'], $dbAddress->is_main_address);
        $this->assertEquals($address['is_active'], $dbAddress->is_active);
        $this->assertTrue($this->user->is($dbAddress->getUpdatedBy()));
    }

    public function test_update_address_multi_status_validation_fails()
    {
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

        $responses = json_decode($response->getContent())->responses;
        $this->assertEquals($address[0]['id'], $responses[0]->id);
        $this->assertEquals(422, $responses[0]->status);
        $this->assertEquals($address[1]['id'], $responses[1]->id);
        $this->assertEquals(422, $responses[1]->status);
        $this->assertEquals($address[2]['id'], $responses[2]->id);
        $this->assertEquals(422, $responses[2]->status);
        $this->assertEquals($address[3]['id'], $responses[3]->id);
        $this->assertEquals(422, $responses[3]->status);
    }

    public function test_update_address_delete_tokens()
    {
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

        $this->assertNotEmpty($dbAddress);
        $this->assertEquals($address['id'], $dbAddress->id);
        $this->assertFalse($dbAddress->can_login);
        $this->assertFalse($dbAddress->tokens()->exists());
    }

    public function test_delete_address()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/addresses/' . $this->addresses[2]->id);
        $response->assertStatus(204);

        $address = $this->addresses[2]->fresh();
        $this->assertNotNull($address->deleted_at);
        $this->assertTrue($this->user->is($address->getDeletedBy()));
    }

    public function test_delete_address_address_not_found()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/addresses/' . ++$this->addresses[3]->id);
        $response->assertStatus(404);
    }
}
