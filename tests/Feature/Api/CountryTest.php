<?php

uses(FluxErp\Tests\Feature\BaseSetup::class);
use Carbon\Carbon;
use FluxErp\Models\Address;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\Country;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Permission;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->language = Language::factory()->create();
    $this->currency = Currency::factory()->create();

    $this->countries = Country::factory()->count(2)->create([
        'language_id' => $this->language->id,
        'currency_id' => $this->currency->id,
    ]);

    $this->permissions = [
        'show' => Permission::findOrCreate('api.countries.{id}.get'),
        'index' => Permission::findOrCreate('api.countries.get'),
        'create' => Permission::findOrCreate('api.countries.post'),
        'update' => Permission::findOrCreate('api.countries.put'),
        'delete' => Permission::findOrCreate('api.countries.{id}.delete'),
    ];
});

test('create country', function (): void {
    $country = [
        'language_id' => $this->language->id,
        'currency_id' => $this->currency->id,
        'name' => 'Country Name',
        'iso_alpha2' => 'FU',
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/countries', $country);
    $response->assertStatus(201);

    $responseCountry = json_decode($response->getContent())->data;
    $dbCountry = Country::query()
        ->whereKey($responseCountry->id)
        ->first();

    expect($dbCountry)->not->toBeEmpty();
    expect($dbCountry->language_id)->toEqual($country['language_id']);
    expect($dbCountry->currency_id)->toEqual($country['currency_id']);
    expect($dbCountry->name)->toEqual($country['name']);
    expect($dbCountry->iso_alpha2)->toEqual($country['iso_alpha2']);
    expect($this->user->is($dbCountry->getCreatedBy()))->toBeTrue();
    expect($this->user->is($dbCountry->getUpdatedBy()))->toBeTrue();
});

test('create country iso alpha2 exists', function (): void {
    $country = [
        'language_id' => $this->language->id,
        'currency_id' => $this->currency->id,
        'name' => 'Country Region Name',
        'iso_alpha2' => $this->countries[0]->iso_alpha2,
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/countries', $country);
    $response->assertStatus(422);
});

test('create country maximum', function (): void {
    $country = [
        'language_id' => $this->language->id,
        'currency_id' => $this->currency->id,
        'name' => 'Country Name',
        'iso_alpha2' => 'FU',
        'iso_alpha3' => 'FOO',
        'iso_numeric' => '007',
        'is_active' => true,
        'is_default' => false,
        'is_eu_country' => true,
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/countries', $country);
    $response->assertStatus(201);

    $responseCountry = json_decode($response->getContent())->data;
    $dbCountry = Country::query()
        ->whereKey($responseCountry->id)
        ->first();

    expect($dbCountry)->not->toBeEmpty();
    expect($dbCountry->language_id)->toEqual($country['language_id']);
    expect($dbCountry->currency_id)->toEqual($country['currency_id']);
    expect($dbCountry->name)->toEqual($country['name']);
    expect($dbCountry->iso_alpha2)->toEqual($country['iso_alpha2']);
    expect($dbCountry->iso_alpha3)->toEqual($country['iso_alpha3']);
    expect($dbCountry->iso_numeric)->toEqual($country['iso_numeric']);
    expect($dbCountry->is_active)->toEqual($country['is_active']);
    expect($dbCountry->is_default)->toEqual($country['is_default']);
    expect($dbCountry->is_eu_country)->toEqual($country['is_eu_country']);
    expect($this->user->is($dbCountry->getCreatedBy()))->toBeTrue();
    expect($this->user->is($dbCountry->getUpdatedBy()))->toBeTrue();
});

test('create country validation fails', function (): void {
    $country = [
        'language_id' => 'language_id',
        'currency_id' => 'currency_id',
        'name' => 'Country Name',
        'iso_alpha2' => 'FU',
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/countries', $country);
    $response->assertStatus(422);
});

test('delete country', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/countries/' . $this->countries[1]->id);
    $response->assertStatus(204);

    $country = $this->countries[1]->fresh();
    expect($country->deleted_at)->not->toBeNull();
    expect($this->user->is($country->getDeletedBy()))->toBeTrue();
});

test('delete country country not found', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/countries/' . ++$this->countries[1]->id);
    $response->assertStatus(404);
});

test('delete country country referenced by address', function (): void {
    $contact = Contact::factory()->create([
        'client_id' => $this->dbClient->getKey(),
    ]);
    Address::factory()->create([
        'client_id' => $contact->client_id,
        'language_id' => $this->language->id,
        'country_id' => $this->countries[1]->id,
        'contact_id' => $contact->id,
    ]);

    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/countries/' . $this->countries[1]->id);
    $response->assertStatus(423);
});

test('delete country country referenced by client', function (): void {
    $client = Client::factory()->create([
        'country_id' => $this->countries[1]->id,
    ]);

    $this->user->clients()->attach($client->id);

    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/countries/' . $this->countries[1]->id);
    $response->assertStatus(423);
});

test('get countries', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/countries');
    $response->assertStatus(200);

    $json = json_decode($response->getContent());
    $jsonCountries = collect($json->data->data);

    // Check the amount of test countries.
    expect(count($jsonCountries))->toBeGreaterThanOrEqual(2);

    // Check if controller returns the test countries.
    foreach ($this->countries as $country) {
        $jsonCountries->contains(function ($jsonCountry) use ($country) {
            return $jsonCountry->id === $country->id &&
                $jsonCountry->language_id === $country->language_id &&
                $jsonCountry->currency_id === $country->currency_id &&
                $jsonCountry->name === $country->name &&
                $jsonCountry->iso_alpha2 === $country->iso_alpha2 &&
                $jsonCountry->iso_alpha3 === $country->iso_alpha3 &&
                $jsonCountry->iso_numeric === $country->iso_numeric &&
                $jsonCountry->is_active === $country->is_active &&
                $jsonCountry->is_default === $country->is_default &&
                $jsonCountry->is_eu_country === $country->is_eu_country &&
                Carbon::parse($jsonCountry->created_at) === Carbon::parse($country->created_at) &&
                Carbon::parse($jsonCountry->updated_at) === Carbon::parse($country->updated_at);
        });
    }
});

test('get country', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/countries/' . $this->countries[0]->id);
    $response->assertStatus(200);

    $json = json_decode($response->getContent());
    $jsonCountry = $json->data;

    // Check if controller returns the test country.
    expect($jsonCountry)->not->toBeEmpty();
    expect($jsonCountry->id)->toEqual($this->countries[0]->id);
    expect($jsonCountry->language_id)->toEqual($this->countries[0]->language_id);
    expect($jsonCountry->currency_id)->toEqual($this->countries[0]->currency_id);
    expect($jsonCountry->name)->toEqual($this->countries[0]->name);
    expect($jsonCountry->iso_alpha2)->toEqual($this->countries[0]->iso_alpha2);
    expect($jsonCountry->iso_alpha3)->toEqual($this->countries[0]->iso_alpha3);
    expect($jsonCountry->iso_numeric)->toEqual($this->countries[0]->iso_numeric);
    expect($jsonCountry->is_active)->toEqual($this->countries[0]->is_active);
    expect($jsonCountry->is_default)->toEqual($this->countries[0]->is_default);
    expect($jsonCountry->is_eu_country)->toEqual($this->countries[0]->is_eu_country);
    expect(Carbon::parse($jsonCountry->created_at))->toEqual(Carbon::parse($this->countries[0]->created_at));
    expect(Carbon::parse($jsonCountry->updated_at))->toEqual(Carbon::parse($this->countries[0]->updated_at));
});

test('get country country not found', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/countries/' . ++$this->countries[1]->id);
    $response->assertStatus(404);
});

test('update country', function (): void {
    $country = [
        'id' => $this->countries[0]->id,
        'name' => uniqid(),
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/countries', $country);
    $response->assertStatus(200);

    $responseCountry = json_decode($response->getContent())->data;
    $dbCountry = Country::query()
        ->whereKey($responseCountry->id)
        ->first();

    expect($dbCountry)->not->toBeEmpty();
    expect($dbCountry->id)->toEqual($country['id']);
    expect($this->user->is($dbCountry->getUpdatedBy()))->toBeTrue();
});

test('update country iso alpha2 exists', function (): void {
    $country = [
        'id' => $this->countries[0]->id,
        'iso_alpha2' => $this->countries[1]->iso_alpha2,
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/countries', $country);
    $response->assertStatus(422);
    expect(json_decode($response->getContent())->status)->toEqual(422);
    expect(property_exists(json_decode($response->getContent())->errors, 'iso_alpha2'))->toBeTrue();
});

test('update country maximum', function (): void {
    $country = [
        'id' => $this->countries[0]->id,
        'language_id' => $this->language->id,
        'currency_id' => $this->currency->id,
        'name' => 'Country Name',
        'iso_alpha2' => 'FU',
        'iso_alpha3' => 'FOO',
        'iso_numeric' => '007',
        'is_active' => true,
        'is_default' => false,
        'is_eu_country' => true,
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/countries', $country);
    $response->assertStatus(200);

    $responseCountry = json_decode($response->getContent())->data;
    $dbCountry = Country::query()
        ->whereKey($responseCountry->id)
        ->first();

    expect($dbCountry)->not->toBeEmpty();
    expect($dbCountry->id)->toEqual($country['id']);
    expect($dbCountry->language_id)->toEqual($country['language_id']);
    expect($dbCountry->currency_id)->toEqual($country['currency_id']);
    expect($dbCountry->name)->toEqual($country['name']);
    expect($dbCountry->iso_alpha2)->toEqual($country['iso_alpha2']);
    expect($dbCountry->iso_alpha3)->toEqual($country['iso_alpha3']);
    expect($dbCountry->iso_numeric)->toEqual($country['iso_numeric']);
    expect($dbCountry->is_active)->toEqual($country['is_active']);
    expect($dbCountry->is_default)->toEqual($country['is_default']);
    expect($dbCountry->is_eu_country)->toEqual($country['is_eu_country']);
    expect($this->user->is($dbCountry->getUpdatedBy()))->toBeTrue();
});

test('update country validation fails', function (): void {
    $country = [
        'id' => $this->countries[0]->id,
        'name' => 42,
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/countries', $country);
    $response->assertStatus(422);
});
