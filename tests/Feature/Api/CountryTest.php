<?php

namespace FluxErp\Tests\Feature\Api;

use Carbon\Carbon;
use FluxErp\Models\Address;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\Country;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Laravel\Sanctum\Sanctum;

class CountryTest extends BaseSetup
{
    use DatabaseTransactions;

    private Model $language;

    private Model $currency;

    private Collection $countries;

    private array $permissions;

    protected function setUp(): void
    {
        parent::setUp();
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
    }

    public function test_get_country()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/countries/' . $this->countries[0]->id);
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $jsonCountry = $json->data;

        // Check if controller returns the test country.
        $this->assertNotEmpty($jsonCountry);
        $this->assertEquals($this->countries[0]->id, $jsonCountry->id);
        $this->assertEquals($this->countries[0]->language_id, $jsonCountry->language_id);
        $this->assertEquals($this->countries[0]->currency_id, $jsonCountry->currency_id);
        $this->assertEquals($this->countries[0]->name, $jsonCountry->name);
        $this->assertEquals($this->countries[0]->iso_alpha2, $jsonCountry->iso_alpha2);
        $this->assertEquals($this->countries[0]->iso_alpha3, $jsonCountry->iso_alpha3);
        $this->assertEquals($this->countries[0]->iso_numeric, $jsonCountry->iso_numeric);
        $this->assertEquals($this->countries[0]->is_active, $jsonCountry->is_active);
        $this->assertEquals($this->countries[0]->is_default, $jsonCountry->is_default);
        $this->assertEquals($this->countries[0]->is_eu_country, $jsonCountry->is_eu_country);
        $this->assertEquals(Carbon::parse($this->countries[0]->created_at),
            Carbon::parse($jsonCountry->created_at));
        $this->assertEquals(Carbon::parse($this->countries[0]->updated_at),
            Carbon::parse($jsonCountry->updated_at));
    }

    public function test_get_country_country_not_found()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/countries/' . ++$this->countries[1]->id);
        $response->assertStatus(404);
    }

    public function test_get_countries()
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/countries');
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $jsonCountries = collect($json->data->data);

        // Check the amount of test countries.
        $this->assertGreaterThanOrEqual(2, count($jsonCountries));

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
    }

    public function test_create_country()
    {
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

        $this->assertNotEmpty($dbCountry);
        $this->assertEquals($country['language_id'], $dbCountry->language_id);
        $this->assertEquals($country['currency_id'], $dbCountry->currency_id);
        $this->assertEquals($country['name'], $dbCountry->name);
        $this->assertEquals($country['iso_alpha2'], $dbCountry->iso_alpha2);
        $this->assertEquals($this->user->id, $dbCountry->created_by->id);
        $this->assertEquals($this->user->id, $dbCountry->updated_by->id);
    }

    public function test_create_country_maximum()
    {
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

        $this->assertNotEmpty($dbCountry);
        $this->assertEquals($country['language_id'], $dbCountry->language_id);
        $this->assertEquals($country['currency_id'], $dbCountry->currency_id);
        $this->assertEquals($country['name'], $dbCountry->name);
        $this->assertEquals($country['iso_alpha2'], $dbCountry->iso_alpha2);
        $this->assertEquals($country['iso_alpha3'], $dbCountry->iso_alpha3);
        $this->assertEquals($country['iso_numeric'], $dbCountry->iso_numeric);
        $this->assertEquals($country['is_active'], $dbCountry->is_active);
        $this->assertEquals($country['is_default'], $dbCountry->is_default);
        $this->assertEquals($country['is_eu_country'], $dbCountry->is_eu_country);
        $this->assertEquals($this->user->id, $dbCountry->created_by->id);
        $this->assertEquals($this->user->id, $dbCountry->updated_by->id);
    }

    public function test_create_country_validation_fails()
    {
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
    }

    public function test_create_country_iso_alpha2_exists()
    {
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
    }

    public function test_update_country()
    {
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

        $this->assertNotEmpty($dbCountry);
        $this->assertEquals($country['id'], $dbCountry->id);
        $this->assertEquals($this->user->id, $dbCountry->updated_by->id);
    }

    public function test_update_country_maximum()
    {
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

        $this->assertNotEmpty($dbCountry);
        $this->assertEquals($country['id'], $dbCountry->id);
        $this->assertEquals($country['language_id'], $dbCountry->language_id);
        $this->assertEquals($country['currency_id'], $dbCountry->currency_id);
        $this->assertEquals($country['name'], $dbCountry->name);
        $this->assertEquals($country['iso_alpha2'], $dbCountry->iso_alpha2);
        $this->assertEquals($country['iso_alpha3'], $dbCountry->iso_alpha3);
        $this->assertEquals($country['iso_numeric'], $dbCountry->iso_numeric);
        $this->assertEquals($country['is_active'], $dbCountry->is_active);
        $this->assertEquals($country['is_default'], $dbCountry->is_default);
        $this->assertEquals($country['is_eu_country'], $dbCountry->is_eu_country);
        $this->assertEquals($this->user->id, $dbCountry->updated_by->id);
    }

    public function test_update_country_validation_fails()
    {
        $country = [
            'id' => $this->countries[0]->id,
            'name' => 42,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/countries', $country);
        $response->assertStatus(422);
    }

    public function test_update_country_iso_alpha2_exists()
    {
        $country = [
            'id' => $this->countries[0]->id,
            'iso_alpha2' => $this->countries[1]->iso_alpha2,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/countries', $country);
        $response->assertStatus(422);
        $this->assertEquals(422, json_decode($response->getContent())->status);
        $this->assertTrue(
            property_exists(json_decode($response->getContent())->errors, 'iso_alpha2')
        );
    }

    public function test_delete_country()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/countries/' . $this->countries[1]->id);
        $response->assertStatus(204);

        $country = $this->countries[1]->fresh();
        $this->assertNotNull($country->deleted_at);
        $this->assertEquals($this->user->id, $country->deleted_by->id);
    }

    public function test_delete_country_country_not_found()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/countries/' . ++$this->countries[1]->id);
        $response->assertStatus(404);
    }

    public function test_delete_country_country_referenced_by_address()
    {
        $contact = Contact::factory()->create([
            'client_id' => $this->dbClient->id,
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
    }

    public function test_delete_country_country_referenced_by_client()
    {
        Client::factory()->create([
            'country_id' => $this->countries[1]->id,
        ]);

        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/countries/' . $this->countries[1]->id);
        $response->assertStatus(423);
    }
}
