<?php

uses(FluxErp\Tests\Feature\BaseSetup::class);
use Carbon\Carbon;
use FluxErp\Models\Country;
use FluxErp\Models\CountryRegion;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Permission;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $language = Language::factory()->create();
    $currency = Currency::factory()->create();

    $this->country = Country::factory()->create([
        'language_id' => $language->id,
        'currency_id' => $currency->id,
    ]);

    $this->countryRegions = CountryRegion::factory()->count(2)->create([
        'country_id' => $this->country->id,
    ]);

    $this->permissions = [
        'show' => Permission::findOrCreate('api.country-regions.{id}.get'),
        'index' => Permission::findOrCreate('api.country-regions.get'),
        'create' => Permission::findOrCreate('api.country-regions.post'),
        'update' => Permission::findOrCreate('api.country-regions.put'),
        'delete' => Permission::findOrCreate('api.country-regions.{id}.delete'),
    ];
});

test('create country region', function (): void {
    $countryRegion = [
        'country_id' => $this->country->id,
        'name' => 'Country Region Name',
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/country-regions', $countryRegion);
    $response->assertStatus(201);

    $responseCountryRegion = json_decode($response->getContent())->data;
    $dbCountryRegion = CountryRegion::query()
        ->whereKey($responseCountryRegion->id)
        ->first();

    expect($dbCountryRegion)->not->toBeEmpty();
    expect($dbCountryRegion->country_id)->toEqual($countryRegion['country_id']);
    expect($dbCountryRegion->name)->toEqual($countryRegion['name']);
    expect($this->user->is($dbCountryRegion->getCreatedBy()))->toBeTrue();
    expect($this->user->is($dbCountryRegion->getUpdatedBy()))->toBeTrue();
});

test('create country region validation fails', function (): void {
    $countryRegion = [
        'country_id' => 'country_id',
        'name' => 'Country Region Name',
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/country-regions', $countryRegion);
    $response->assertStatus(422);
});

test('delete country region', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/country-regions/' . $this->countryRegions[1]->id);
    $response->assertStatus(204);

    $countryRegion = $this->countryRegions[1]->fresh();
    expect($countryRegion->deleted_at)->not->toBeNull();
    expect($this->user->is($countryRegion->getDeletedBy()))->toBeTrue();
});

test('delete country region country region not found', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/country-regions/' . ++$this->countryRegions[1]->id);
    $response->assertStatus(404);
});

test('get country region', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/country-regions/' . $this->countryRegions[0]->id);
    $response->assertStatus(200);

    $json = json_decode($response->getContent());
    $jsonCountryRegion = $json->data;

    // Check if controller returns the test country region.
    expect($jsonCountryRegion)->not->toBeEmpty();
    expect($jsonCountryRegion->id)->toEqual($this->countryRegions[0]->id);
    expect($jsonCountryRegion->country_id)->toEqual($this->countryRegions[0]->country_id);
    expect($jsonCountryRegion->name)->toEqual($this->countryRegions[0]->name);
    expect(Carbon::parse($jsonCountryRegion->created_at))->toEqual(Carbon::parse($this->countryRegions[0]->created_at));
    expect(Carbon::parse($jsonCountryRegion->updated_at))->toEqual(Carbon::parse($this->countryRegions[0]->updated_at));
});

test('get country region country region not found', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/country-regions/' . ++$this->countryRegions[1]->id);
    $response->assertStatus(404);
});

test('get country regions', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/country-regions');
    $response->assertStatus(200);

    $json = json_decode($response->getContent());
    $jsonCountryRegions = collect($json->data->data);

    // Check the amount of test country regions.
    expect(count($jsonCountryRegions))->toBeGreaterThanOrEqual(2);

    // Check if controller returns the test country regions.
    foreach ($this->countryRegions as $countryRegion) {
        $jsonCountryRegions->contains(function ($jsonCountryRegion) use ($countryRegion) {
            return $jsonCountryRegion->id === $countryRegion->id &&
                $jsonCountryRegion->country_id === $countryRegion->country_id &&
                $jsonCountryRegion->name === $countryRegion->name &&
                Carbon::parse($jsonCountryRegion->created_at) === Carbon::parse($countryRegion->created_at) &&
                Carbon::parse($jsonCountryRegion->updated_at) === Carbon::parse($countryRegion->updated_at);
        });
    }
});

test('update country region', function (): void {
    $countryRegion = [
        'id' => $this->countryRegions[0]->id,
        'name' => uniqid(),
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/country-regions', $countryRegion);
    $response->assertStatus(200);

    $responseCountryRegion = json_decode($response->getContent())->data;
    $dbCountryRegion = CountryRegion::query()
        ->whereKey($responseCountryRegion->id)
        ->first();

    expect($dbCountryRegion)->not->toBeEmpty();
    expect($dbCountryRegion->id)->toEqual($countryRegion['id']);
    expect($dbCountryRegion->name)->toEqual($countryRegion['name']);
    expect($this->user->is($dbCountryRegion->getUpdatedBy()))->toBeTrue();
});

test('update country region maximum', function (): void {
    $countryRegion = [
        'id' => $this->countryRegions[0]->id,
        'country_id' => $this->country->id,
        'name' => 'Foo Bar',
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/country-regions', $countryRegion);
    $response->assertStatus(200);

    $responseCountryRegion = json_decode($response->getContent())->data;
    $dbCountryRegion = CountryRegion::query()
        ->whereKey($responseCountryRegion->id)
        ->first();

    expect($dbCountryRegion)->not->toBeEmpty();
    expect($dbCountryRegion->id)->toEqual($countryRegion['id']);
    expect($dbCountryRegion->country_id)->toEqual($countryRegion['country_id']);
    expect($dbCountryRegion->name)->toEqual($countryRegion['name']);
    expect($this->user->is($dbCountryRegion->getUpdatedBy()))->toBeTrue();
});

test('update country region validation fails', function (): void {
    $countryRegions = [
        'id' => $this->countryRegions[0]->id,
        'name' => 42,
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/country-regions', $countryRegions);
    $response->assertStatus(422);
});
