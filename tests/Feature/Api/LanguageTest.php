<?php

namespace FluxErp\Tests\Feature\Api;

use Carbon\Carbon;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Country;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Permission;
use FluxErp\Models\User;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Laravel\Sanctum\Sanctum;

class LanguageTest extends BaseSetup
{
    use DatabaseTransactions;

    private Collection $languages;

    private array $permissions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->languages = Language::factory()->count(2)->create();

        $this->permissions = [
            'show' => Permission::findOrCreate('api.languages.{id}.get'),
            'index' => Permission::findOrCreate('api.languages.get'),
            'create' => Permission::findOrCreate('api.languages.post'),
            'update' => Permission::findOrCreate('api.languages.put'),
            'delete' => Permission::findOrCreate('api.languages.{id}.delete'),
        ];
    }

    public function test_get_language()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/languages/' . $this->languages[0]->id);
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $jsonLanguage = $json->data;

        // Check if controller returns the test language.
        $this->assertNotEmpty($jsonLanguage);
        $this->assertEquals($this->languages[0]->id, $jsonLanguage->id);
        $this->assertEquals($this->languages[0]->name, $jsonLanguage->name);
        $this->assertEquals($this->languages[0]->iso_name, $jsonLanguage->iso_name);
        $this->assertEquals($this->languages[0]->language_code, $jsonLanguage->language_code);
        $this->assertEquals(Carbon::parse($this->languages[0]->created_at),
            Carbon::parse($jsonLanguage->created_at));
        $this->assertEquals(Carbon::parse($this->languages[0]->updated_at),
            Carbon::parse($jsonLanguage->updated_at));
    }

    public function test_get_language_language_not_found()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/languages/' . ++$this->languages[1]->id);
        $response->assertStatus(404);
    }

    public function test_get_languages()
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/languages');
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $jsonLanguages = collect($json->data->data);

        // Check the amount of test languages.
        $this->assertGreaterThanOrEqual(2, count($jsonLanguages));

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
    }

    public function test_create_language()
    {
        $language = [
            'name' => 'Language Name',
            'iso_name' => 'Language ISO',
            'language_code' => 'foo_BAR',
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/languages', $language);
        $response->assertStatus(201);

        $responseLanguage = json_decode($response->getContent())->data;
        $dbLanguage = Language::query()
            ->whereKey($responseLanguage->id)
            ->first();

        $this->assertNotEmpty($dbLanguage);
        $this->assertEquals($language['name'], $dbLanguage->name);
        $this->assertEquals($language['iso_name'], $dbLanguage->iso_name);
        $this->assertEquals($language['language_code'], $dbLanguage->language_code);
        $this->assertTrue($this->user->is($dbLanguage->getCreatedBy()));
        $this->assertTrue($this->user->is($dbLanguage->getUpdatedBy()));
    }

    public function test_create_language_validation_fails()
    {
        $language = [
            'name' => 42,
            'iso_name' => 42,
            'language_code' => 42,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/languages', $language);
        $response->assertStatus(422);
    }

    public function test_create_language_language_code_exists()
    {
        $language = [
            'name' => 'Language Name',
            'iso_name' => 'Language ISO',
            'language_code' => $this->languages[0]->language_code,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/languages', $language);
        $response->assertStatus(422);
    }

    public function test_update_language()
    {
        $language = [
            'id' => $this->languages[0]->id,
            'name' => uniqid(),
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/languages', $language);
        $response->assertStatus(200);

        $responseLanguage = json_decode($response->getContent())->data;
        $dbLanguage = Language::query()
            ->whereKey($responseLanguage->id)
            ->first();

        $this->assertNotEmpty($dbLanguage);
        $this->assertEquals($language['id'], $dbLanguage->id);
        $this->assertEquals($language['name'], $dbLanguage->name);
        $this->assertTrue($this->user->is($dbLanguage->getUpdatedBy()));
    }

    public function test_update_language_maximum()
    {
        $language = [
            'id' => $this->languages[0]->id,
            'name' => 'Language Name',
            'iso_name' => 'Language ISO',
            'language_code' => 'foo_BAR',
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/languages', $language);
        $response->assertStatus(200);

        $responseLanguage = json_decode($response->getContent())->data;
        $dbLanguage = Language::query()
            ->whereKey($responseLanguage->id)
            ->first();

        $this->assertNotEmpty($dbLanguage);
        $this->assertEquals($language['id'], $dbLanguage->id);
        $this->assertEquals($language['name'], $dbLanguage->name);
        $this->assertEquals($language['iso_name'], $dbLanguage->iso_name);
        $this->assertEquals($language['language_code'], $dbLanguage->language_code);
        $this->assertTrue($this->user->is($dbLanguage->getUpdatedBy()));
    }

    public function test_update_language_validation_fails()
    {
        $language = [
            'id' => $this->languages[0]->id,
            'name' => 42,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/languages', $language);
        $response->assertStatus(422);
    }

    public function test_update_language_language_code_exists()
    {
        $language = [
            'id' => $this->languages[0]->id,
            'language_code' => $this->languages[1]->language_code,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/languages', $language);
        $response->assertStatus(422);
        $this->assertEquals(422, json_decode($response->getContent())->status);
        $this->assertTrue(
            property_exists(json_decode($response->getContent())->errors, 'language_code')
        );
    }

    public function test_delete_language()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/languages/' . $this->languages[1]->id);
        $response->assertStatus(204);

        $language = $this->languages[1]->fresh();
        $this->assertNotNull($language->deleted_at);
        $this->assertTrue($this->user->is($language->getDeletedBy()));
    }

    public function test_delete_language_language_not_found()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/languages/' . ++$this->languages[1]->id);
        $response->assertStatus(404);
    }

    public function test_delete_language_language_referenced_by_address()
    {
        $currency = Currency::factory()->create();
        $country = Country::factory()->create([
            'language_id' => $this->languages[1]->id,
            'currency_id' => $currency->id,
        ]);
        $contact = Contact::factory()->create([
            'client_id' => $this->dbClient->getKey(),
        ]);
        Address::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'language_id' => $this->languages[1]->id,
            'country_id' => $country->id,
            'contact_id' => $contact->id,
            'is_main_address' => false,
        ]);

        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/languages/' . $this->languages[1]->id);
        $response->assertStatus(423);
    }

    public function test_delete_language_language_referenced_by_user()
    {
        User::factory()->create([
            'language_id' => $this->languages[1]->id,
        ]);

        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/languages/' . $this->languages[1]->id);
        $response->assertStatus(423);
    }
}
