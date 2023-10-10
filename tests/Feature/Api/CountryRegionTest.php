<?php

namespace FluxErp\Tests\Feature\Api;

use Carbon\Carbon;
use FluxErp\Models\Country;
use FluxErp\Models\CountryRegion;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;

class CountryRegionTest extends BaseSetup
{
    use DatabaseTransactions;

    private Model $country;

    private Collection $countryRegions;

    private array $permissions;

    protected function setUp(): void
    {
        parent::setUp();
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

        $this->app->make(PermissionRegistrar::class)->registerPermissions();
    }

    public function test_get_country_region()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/country-regions/' . $this->countryRegions[0]->id);
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $jsonCountryRegion = $json->data;

        // Check if controller returns the test country region.
        $this->assertNotEmpty($jsonCountryRegion);
        $this->assertEquals($this->countryRegions[0]->id, $jsonCountryRegion->id);
        $this->assertEquals($this->countryRegions[0]->country_id, $jsonCountryRegion->country_id);
        $this->assertEquals($this->countryRegions[0]->name, $jsonCountryRegion->name);
        $this->assertEquals(Carbon::parse($this->countryRegions[0]->created_at),
            Carbon::parse($jsonCountryRegion->created_at));
        $this->assertEquals(Carbon::parse($this->countryRegions[0]->updated_at),
            Carbon::parse($jsonCountryRegion->updated_at));
    }

    public function test_get_country_region_country_region_not_found()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/country-regions/' . ++$this->countryRegions[1]->id);
        $response->assertStatus(404);
    }

    public function test_get_country_regions()
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/country-regions');
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $jsonCountryRegions = collect($json->data->data);

        // Check the amount of test country regions.
        $this->assertGreaterThanOrEqual(2, count($jsonCountryRegions));

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
    }

    public function test_create_country_region()
    {
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

        $this->assertNotEmpty($dbCountryRegion);
        $this->assertEquals($countryRegion['country_id'], $dbCountryRegion->country_id);
        $this->assertEquals($countryRegion['name'], $dbCountryRegion->name);
        $this->assertEquals($this->user->id, $dbCountryRegion->created_by->id);
        $this->assertEquals($this->user->id, $dbCountryRegion->updated_by->id);
    }

    public function test_create_country_region_validation_fails()
    {
        $countryRegion = [
            'country_id' => 'country_id',
            'name' => 'Country Region Name',
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/country-regions', $countryRegion);
        $response->assertStatus(422);
    }

    public function test_update_country_region()
    {
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

        $this->assertNotEmpty($dbCountryRegion);
        $this->assertEquals($countryRegion['id'], $dbCountryRegion->id);
        $this->assertEquals($countryRegion['name'], $dbCountryRegion->name);
        $this->assertEquals($this->user->id, $dbCountryRegion->updated_by->id);
    }

    public function test_update_country_region_maximum()
    {
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

        $this->assertNotEmpty($dbCountryRegion);
        $this->assertEquals($countryRegion['id'], $dbCountryRegion->id);
        $this->assertEquals($countryRegion['country_id'], $dbCountryRegion->country_id);
        $this->assertEquals($countryRegion['name'], $dbCountryRegion->name);
        $this->assertEquals($this->user->id, $dbCountryRegion->updated_by->id);
    }

    public function test_update_country_region_validation_fails()
    {
        $countryRegions = [
            'id' => $this->countryRegions[0]->id,
            'name' => 42,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/country-regions', $countryRegions);
        $response->assertStatus(422);
    }

    public function test_delete_country_region()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/country-regions/' . $this->countryRegions[1]->id);
        $response->assertStatus(204);

        $countryRegion = $this->countryRegions[1]->fresh();
        $this->assertNotNull($countryRegion->deleted_at);
        $this->assertEquals($this->user->id, $countryRegion->deleted_by->id);
    }

    public function test_delete_country_region_country_region_not_found()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/country-regions/' . ++$this->countryRegions[1]->id);
        $response->assertStatus(404);
    }
}
