<?php

namespace FluxErp\Tests\Feature\Api;

use Carbon\Carbon;
use FluxErp\Models\Country;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;

class CurrencyTest extends BaseSetup
{
    use DatabaseTransactions;

    private Collection $currencies;

    private array $permissions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->currencies = Currency::factory()->count(2)->create();

        $this->permissions = [
            'show' => Permission::findOrCreate('api.currencies.{id}.get'),
            'index' => Permission::findOrCreate('api.currencies.get'),
            'create' => Permission::findOrCreate('api.currencies.post'),
            'update' => Permission::findOrCreate('api.currencies.put'),
            'delete' => Permission::findOrCreate('api.currencies.{id}.delete'),
        ];
    }

    public function test_get_currency()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/currencies/' . $this->currencies[0]->id);
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $jsonCurrency = $json->data;

        // Check if controller returns the test currency.
        $this->assertNotEmpty($jsonCurrency);
        $this->assertEquals($this->currencies[0]->id, $jsonCurrency->id);
        $this->assertEquals($this->currencies[0]->name, $jsonCurrency->name);
        $this->assertEquals($this->currencies[0]->iso, $jsonCurrency->iso);
        $this->assertEquals($this->currencies[0]->symbol, $jsonCurrency->symbol);
        $this->assertEquals($this->currencies[0]->is_default, $jsonCurrency->is_default);
        $this->assertEquals(Carbon::parse($this->currencies[0]->created_at),
            Carbon::parse($jsonCurrency->created_at));
        $this->assertEquals(Carbon::parse($this->currencies[0]->updated_at),
            Carbon::parse($jsonCurrency->updated_at));
    }

    public function test_get_currency_currency_not_found()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/currencies/' . ++$this->currencies[1]->id);
        $response->assertStatus(404);
    }

    public function test_get_currencies()
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/currencies');
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $jsonCurrencies = collect($json->data->data);

        // Check the amount of test currencies.
        $this->assertGreaterThanOrEqual(2, count($jsonCurrencies));

        // Check if controller returns the test currencies.
        foreach ($this->currencies as $currency) {
            $jsonCurrencies->contains(function ($jsonCurrency) use ($currency) {
                return $jsonCurrency->id === $currency->id &&
                    $jsonCurrency->name === $currency->name &&
                    $jsonCurrency->iso === $currency->iso &&
                    $jsonCurrency->symbol === $currency->symbol &&
                    $jsonCurrency->is_default === $currency->is_default &&
                    Carbon::parse($jsonCurrency->created_at) === Carbon::parse($currency->created_at) &&
                    Carbon::parse($jsonCurrency->updated_at) === Carbon::parse($currency->updated_at);
            });
        }
    }

    public function test_create_currency()
    {
        $currency = [
            'name' => 'Currency Name',
            'iso' => 'ISO',
            'symbol' => '§',
            'is_default' => true,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/currencies', $currency);
        $response->assertStatus(201);

        $responseCurrency = json_decode($response->getContent())->data;
        $dbCurrency = Currency::query()
            ->whereKey($responseCurrency->id)
            ->first();

        $this->assertNotEmpty($dbCurrency);
        $this->assertEquals($currency['name'], $dbCurrency->name);
        $this->assertEquals($currency['iso'], $dbCurrency->iso);
        $this->assertEquals($currency['symbol'], $dbCurrency->symbol);
        $this->assertEquals($currency['is_default'], $dbCurrency->is_default);
        $this->assertEquals($this->user->id, $dbCurrency->created_by->id);
        $this->assertEquals($this->user->id, $dbCurrency->updated_by->id);
    }

    public function test_create_currency_validation_fails()
    {
        $currency = [
            'name' => 42,
            'iso' => 42,
            'symbol' => 42,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/currencies', $currency);
        $response->assertStatus(422);
    }

    public function test_create_currency_iso_exists()
    {
        $currency = [
            'name' => 'Currency Name',
            'iso' => $this->currencies[0]->iso,
            'symbol' => '§',
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/currencies', $currency);
        $response->assertStatus(422);
    }

    public function test_update_currency()
    {
        $currency = [
            'id' => $this->currencies[0]->id,
            'name' => uniqid(),
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/currencies', $currency);
        $response->assertStatus(200);

        $responseCurrency = json_decode($response->getContent())->data;
        $dbCurrency = Currency::query()
            ->whereKey($responseCurrency->id)
            ->first();

        $this->assertNotEmpty($dbCurrency);
        $this->assertEquals($currency['id'], $dbCurrency->id);
        $this->assertEquals($currency['name'], $dbCurrency->name);
        $this->assertEquals($this->user->id, $dbCurrency->updated_by->id);
    }

    public function test_update_currency_maximum()
    {
        $currency = [
            'id' => $this->currencies[0]->id,
            'name' => 'Currency Name',
            'iso' => 'FOO',
            'symbol' => 'µ',
            'is_default' => true,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/currencies', $currency);
        $response->assertStatus(200);

        $responseCurrency = json_decode($response->getContent())->data;
        $dbCurrency = Currency::query()
            ->whereKey($responseCurrency->id)
            ->first();

        $this->assertNotEmpty($dbCurrency);
        $this->assertEquals($currency['id'], $dbCurrency->id);
        $this->assertEquals($currency['name'], $dbCurrency->name);
        $this->assertEquals($currency['iso'], $dbCurrency->iso);
        $this->assertEquals($currency['symbol'], $dbCurrency->symbol);
        $this->assertEquals($currency['is_default'], $dbCurrency->is_default);
        $this->assertEquals($this->user->id, $dbCurrency->updated_by->id);
    }

    public function test_update_currency_validation_fails()
    {
        $currency = [
            'id' => $this->currencies[0]->id,
            'name' => 42,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/currencies', $currency);
        $response->assertStatus(422);
    }

    public function test_update_currency_iso_exists()
    {
        $currency = [
            'id' => $this->currencies[0]->id,
            'iso' => $this->currencies[1]->iso,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/currencies', $currency);
        $response->assertStatus(422);
        $this->assertEquals(422, json_decode($response->getContent())->status);
        $this->assertTrue(
            property_exists(json_decode($response->getContent())->errors, 'iso')
        );
    }

    public function test_delete_currency()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/currencies/' . $this->currencies[1]->id);
        $response->assertStatus(204);

        $currency = $this->currencies[1]->fresh();
        $this->assertNotNull($currency->deleted_at);
        $this->assertEquals($this->user->id, $currency->deleted_by->id);
    }

    public function test_delete_currency_currency_not_found()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/currencies/' . ++$this->currencies[1]->id);
        $response->assertStatus(404);
    }

    public function test_delete_currency_currency_referenced_by_country()
    {
        $language = Language::factory()->create();
        Country::factory()->create([
            'language_id' => $language->id,
            'currency_id' => $this->currencies[1]->id,
        ]);

        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/currencies/' . $this->currencies[1]->id);
        $response->assertStatus(423);
    }
}
