<?php

use Carbon\Carbon;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Country;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Permission;
use FluxErp\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->languages = Language::factory()->count(2)->create();

    $this->permissions = [
        'show' => Permission::findOrCreate('api.languages.{id}.get'),
        'index' => Permission::findOrCreate('api.languages.get'),
        'create' => Permission::findOrCreate('api.languages.post'),
        'update' => Permission::findOrCreate('api.languages.put'),
        'delete' => Permission::findOrCreate('api.languages.{id}.delete'),
    ];
});

test('create language', function (): void {
    $language = [
        'name' => 'Language Name',
        'iso_name' => 'Language ISO',
        'language_code' => 'foo_BAR',
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/languages', $language);
    $response->assertCreated();

    $responseLanguage = json_decode($response->getContent())->data;
    $dbLanguage = Language::query()
        ->whereKey($responseLanguage->id)
        ->first();

    expect($dbLanguage)->not->toBeEmpty();
    expect($dbLanguage->name)->toEqual($language['name']);
    expect($dbLanguage->iso_name)->toEqual($language['iso_name']);
    expect($dbLanguage->language_code)->toEqual($language['language_code']);
    expect($this->user->is($dbLanguage->getCreatedBy()))->toBeTrue();
    expect($this->user->is($dbLanguage->getUpdatedBy()))->toBeTrue();
});

test('create language language code exists', function (): void {
    $language = [
        'name' => 'Language Name',
        'iso_name' => 'Language ISO',
        'language_code' => $this->languages[0]->language_code,
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/languages', $language);
    $response->assertUnprocessable();
});

test('create language validation fails', function (): void {
    $language = [
        'name' => 42,
        'iso_name' => 42,
        'language_code' => 42,
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/languages', $language);
    $response->assertUnprocessable();
});

test('delete language', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/languages/' . $this->languages[1]->id);
    $response->assertNoContent();

    $language = $this->languages[1]->fresh();
    expect($language->deleted_at)->not->toBeNull();
    expect($this->user->is($language->getDeletedBy()))->toBeTrue();
});

test('delete language language not found', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/languages/' . ++$this->languages[1]->id);
    $response->assertNotFound();
});

test('delete language language referenced by address', function (): void {
    $currency = Currency::factory()->create();
    $country = Country::factory()->create([
        'language_id' => $this->languages[1]->id,
        'currency_id' => $currency->id,
    ]);
    $contact = Contact::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);
    Address::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'language_id' => $this->languages[1]->id,
        'country_id' => $country->id,
        'contact_id' => $contact->id,
        'is_main_address' => false,
    ]);

    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/languages/' . $this->languages[1]->id);
    $response->assertStatus(423);
});

test('delete language language referenced by user', function (): void {
    User::factory()->create([
        'language_id' => $this->languages[1]->id,
    ]);

    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/languages/' . $this->languages[1]->id);
    $response->assertStatus(423);
});

test('get language', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/languages/' . $this->languages[0]->id);
    $response->assertOk();

    $json = json_decode($response->getContent());
    $jsonLanguage = $json->data;

    // Check if controller returns the test language.
    expect($jsonLanguage)->not->toBeEmpty();
    expect($jsonLanguage->id)->toEqual($this->languages[0]->id);
    expect($jsonLanguage->name)->toEqual($this->languages[0]->name);
    expect($jsonLanguage->iso_name)->toEqual($this->languages[0]->iso_name);
    expect($jsonLanguage->language_code)->toEqual($this->languages[0]->language_code);
    expect(Carbon::parse($jsonLanguage->created_at))->toEqual(Carbon::parse($this->languages[0]->created_at));
    expect(Carbon::parse($jsonLanguage->updated_at))->toEqual(Carbon::parse($this->languages[0]->updated_at));
});

test('get language language not found', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/languages/' . ++$this->languages[1]->id);
    $response->assertNotFound();
});

test('get languages', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/languages');
    $response->assertOk();

    $json = json_decode($response->getContent());
    $jsonLanguages = collect($json->data->data);

    // Check the amount of test languages.
    expect(count($jsonLanguages))->toBeGreaterThanOrEqual(2);

    // Check if controller returns the test languages.
    foreach ($this->languages as $language) {
        $jsonLanguages->contains(function ($jsonLanguage) use ($language) {
            return $jsonLanguage->id === $language->id &&
                $jsonLanguage->name === $language->name &&
                $jsonLanguage->iso_name === $language->iso_name &&
                $jsonLanguage->language_code === $language->language_code &&
                Carbon::parse($jsonLanguage->created_at) === Carbon::parse($language->created_at) &&
                Carbon::parse($jsonLanguage->updated_at) === Carbon::parse($language->updated_at);
        });
    }
});

test('update language', function (): void {
    $language = [
        'id' => $this->languages[0]->id,
        'name' => uniqid(),
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/languages', $language);
    $response->assertOk();

    $responseLanguage = json_decode($response->getContent())->data;
    $dbLanguage = Language::query()
        ->whereKey($responseLanguage->id)
        ->first();

    expect($dbLanguage)->not->toBeEmpty();
    expect($dbLanguage->id)->toEqual($language['id']);
    expect($dbLanguage->name)->toEqual($language['name']);
    expect($this->user->is($dbLanguage->getUpdatedBy()))->toBeTrue();
});

test('update language language code exists', function (): void {
    $language = [
        'id' => $this->languages[0]->id,
        'language_code' => $this->languages[1]->language_code,
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/languages', $language);
    $response->assertUnprocessable();
    expect(json_decode($response->getContent())->status)->toEqual(422);
    expect(property_exists(json_decode($response->getContent())->errors, 'language_code'))->toBeTrue();
});

test('update language maximum', function (): void {
    $language = [
        'id' => $this->languages[0]->id,
        'name' => 'Language Name',
        'iso_name' => 'Language ISO',
        'language_code' => 'foo_BAR',
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/languages', $language);
    $response->assertOk();

    $responseLanguage = json_decode($response->getContent())->data;
    $dbLanguage = Language::query()
        ->whereKey($responseLanguage->id)
        ->first();

    expect($dbLanguage)->not->toBeEmpty();
    expect($dbLanguage->id)->toEqual($language['id']);
    expect($dbLanguage->name)->toEqual($language['name']);
    expect($dbLanguage->iso_name)->toEqual($language['iso_name']);
    expect($dbLanguage->language_code)->toEqual($language['language_code']);
    expect($this->user->is($dbLanguage->getUpdatedBy()))->toBeTrue();
});

test('update language validation fails', function (): void {
    $language = [
        'id' => $this->languages[0]->id,
        'name' => 42,
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/languages', $language);
    $response->assertUnprocessable();
});
