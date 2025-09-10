<?php

use Carbon\Carbon;
use FluxErp\Models\Country;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Permission;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->currencies = Currency::factory()->count(2)->create();

    $this->permissions = [
        'show' => Permission::findOrCreate('api.currencies.{id}.get'),
        'index' => Permission::findOrCreate('api.currencies.get'),
        'create' => Permission::findOrCreate('api.currencies.post'),
        'update' => Permission::findOrCreate('api.currencies.put'),
        'delete' => Permission::findOrCreate('api.currencies.{id}.delete'),
    ];
});

test('create currency', function (): void {
    $currency = [
        'name' => 'Currency Name',
        'iso' => 'ISO',
        'symbol' => '§',
        'is_default' => true,
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/currencies', $currency);
    $response->assertCreated();

    $responseCurrency = json_decode($response->getContent())->data;
    $dbCurrency = Currency::query()
        ->whereKey($responseCurrency->id)
        ->first();

    expect($dbCurrency)->not->toBeEmpty();
    expect($dbCurrency->name)->toEqual($currency['name']);
    expect($dbCurrency->iso)->toEqual($currency['iso']);
    expect($dbCurrency->symbol)->toEqual($currency['symbol']);
    expect($dbCurrency->is_default)->toEqual($currency['is_default']);
    expect($this->user->is($dbCurrency->getCreatedBy()))->toBeTrue();
    expect($this->user->is($dbCurrency->getUpdatedBy()))->toBeTrue();
});

test('create currency iso exists', function (): void {
    $currency = [
        'name' => 'Currency Name',
        'iso' => $this->currencies[0]->iso,
        'symbol' => '§',
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/currencies', $currency);
    $response->assertUnprocessable();
});

test('create currency validation fails', function (): void {
    $currency = [
        'name' => 42,
        'iso' => 42,
        'symbol' => 42,
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/currencies', $currency);
    $response->assertUnprocessable();
});

test('delete currency', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/currencies/' . $this->currencies[1]->id);
    $response->assertNoContent();

    $currency = $this->currencies[1]->fresh();
    expect($currency->deleted_at)->not->toBeNull();
    expect($this->user->is($currency->getDeletedBy()))->toBeTrue();
});

test('delete currency currency not found', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/currencies/' . ++$this->currencies[1]->id);
    $response->assertNotFound();
});

test('delete currency currency referenced by country', function (): void {
    $language = Language::factory()->create();
    Country::factory()->create([
        'language_id' => $language->id,
        'currency_id' => $this->currencies[1]->id,
    ]);

    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/currencies/' . $this->currencies[1]->id);
    $response->assertStatus(423);
});

test('get currencies', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/currencies');
    $response->assertOk();

    $json = json_decode($response->getContent());
    $jsonCurrencies = collect($json->data->data);

    // Check the amount of test currencies.
    expect(count($jsonCurrencies))->toBeGreaterThanOrEqual(2);

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
});

test('get currency', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/currencies/' . $this->currencies[0]->id);
    $response->assertOk();

    $json = json_decode($response->getContent());
    $jsonCurrency = $json->data;

    // Check if controller returns the test currency.
    expect($jsonCurrency)->not->toBeEmpty();
    expect($jsonCurrency->id)->toEqual($this->currencies[0]->id);
    expect($jsonCurrency->name)->toEqual($this->currencies[0]->name);
    expect($jsonCurrency->iso)->toEqual($this->currencies[0]->iso);
    expect($jsonCurrency->symbol)->toEqual($this->currencies[0]->symbol);
    expect($jsonCurrency->is_default)->toEqual($this->currencies[0]->is_default);
    expect(Carbon::parse($jsonCurrency->created_at))->toEqual(Carbon::parse($this->currencies[0]->created_at));
    expect(Carbon::parse($jsonCurrency->updated_at))->toEqual(Carbon::parse($this->currencies[0]->updated_at));
});

test('get currency currency not found', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/currencies/' . ++$this->currencies[1]->id);
    $response->assertNotFound();
});

test('update currency', function (): void {
    $currency = [
        'id' => $this->currencies[0]->id,
        'name' => uniqid(),
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/currencies', $currency);
    $response->assertOk();

    $responseCurrency = json_decode($response->getContent())->data;
    $dbCurrency = Currency::query()
        ->whereKey($responseCurrency->id)
        ->first();

    expect($dbCurrency)->not->toBeEmpty();
    expect($dbCurrency->id)->toEqual($currency['id']);
    expect($dbCurrency->name)->toEqual($currency['name']);
    expect($this->user->is($dbCurrency->getUpdatedBy()))->toBeTrue();
});

test('update currency iso exists', function (): void {
    $currency = [
        'id' => $this->currencies[0]->id,
        'iso' => $this->currencies[1]->iso,
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/currencies', $currency);
    $response->assertUnprocessable();
    expect(json_decode($response->getContent())->status)->toEqual(422);
    expect(property_exists(json_decode($response->getContent())->errors, 'iso'))->toBeTrue();
});

test('update currency maximum', function (): void {
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
    $response->assertOk();

    $responseCurrency = json_decode($response->getContent())->data;
    $dbCurrency = Currency::query()
        ->whereKey($responseCurrency->id)
        ->first();

    expect($dbCurrency)->not->toBeEmpty();
    expect($dbCurrency->id)->toEqual($currency['id']);
    expect($dbCurrency->name)->toEqual($currency['name']);
    expect($dbCurrency->iso)->toEqual($currency['iso']);
    expect($dbCurrency->symbol)->toEqual($currency['symbol']);
    expect($dbCurrency->is_default)->toEqual($currency['is_default']);
    expect($this->user->is($dbCurrency->getUpdatedBy()))->toBeTrue();
});

test('update currency validation fails', function (): void {
    $currency = [
        'id' => $this->currencies[0]->id,
        'name' => 42,
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/currencies', $currency);
    $response->assertUnprocessable();
});
