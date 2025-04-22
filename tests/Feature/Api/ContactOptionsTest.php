<?php

namespace FluxErp\Tests\Feature\Api;

use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\ContactOption;
use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Support\Collection;
use Laravel\Sanctum\Sanctum;

class ContactOptionsTest extends BaseSetup
{
    private Collection $contactOptions;

    private array $permissions;

    protected function setUp(): void
    {
        parent::setUp();

        $contact = Contact::factory()->create([
            'client_id' => $this->dbClient->getKey(),
        ]);

        $this->address = Address::factory()->create([
            'contact_id' => $contact->id,
            'client_id' => $this->dbClient->getKey(),
        ]);
        $this->contactOptions = ContactOption::factory()->count(2)->create([
            'address_id' => $this->address->id,
            'type' => 'email',
            'label' => 'Test Label',
            'value' => 'testmail@gmail.com',
        ]);

        $this->permissions = [
            'show' => Permission::findOrCreate('api.contact-options.{id}.get'),
            'index' => Permission::findOrCreate('api.contact-options.get'),
            'create' => Permission::findOrCreate('api.contact-options.post'),
            'update' => Permission::findOrCreate('api.contact-options.put'),
            'delete' => Permission::findOrCreate('api.contact-options.delete'),
        ];
    }

    public function test_create_contact_option(): void
    {
        $contactOption = [
            'address_id' => $this->address->id,
            'type' => 'email',
            'label' => 'Test Label',
            'value' => 'testmail@gmail.com',
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/contact-options', $contactOption);
        $response->assertStatus(201);

        $responseContactOption = json_decode($response->getContent())->data;
        $dbContactOption = ContactOption::query()
            ->whereKey($responseContactOption->id)
            ->first();

        $this->assertNotEmpty($dbContactOption);
        $this->assertEquals($contactOption['type'], $dbContactOption->type);
        $this->assertTrue($this->user->is($dbContactOption->getCreatedBy()));
        $this->assertTrue($this->user->is($dbContactOption->getUpdatedBy()));
    }

    public function test_create_contact_option_validation_fails(): void
    {
        $contactOption = [
            'type' => 42,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/contact-options', $contactOption);
        $response->assertStatus(422);
    }

    public function test_delete_contact_option(): void
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/contact-options/' . $this->contactOptions[0]->id);
        $response->assertStatus(204);

        $this->assertDatabaseMissing('contact_options', ['id' => $this->contactOptions[0]->id]);
    }

    public function test_delete_contact_option_not_found(): void
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $this->contactOptions[0]->delete();

        $response = $this->actingAs($this->user)->delete('/api/contact-options/' . $this->contactOptions[0]->id);
        $response->assertStatus(404);
    }

    public function test_get_contact_option(): void
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/contact-options');
        $response->assertStatus(200);

        $responseContactOptions = json_decode($response->getContent())->data;

        $this->assertCount(2, $responseContactOptions->data);
    }

    public function test_get_specific_contact_option(): void
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/contact-options/' . $this->contactOptions[0]->id);
        $response->assertStatus(200);

        $responseContactOptions = json_decode($response->getContent())->data;

        $this->assertEquals($this->contactOptions[0]->id, $responseContactOptions->id);
    }

    public function test_get_specific_contact_option_not_found(): void
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $this->contactOptions[0]->delete();

        $response = $this->actingAs($this->user)->get('/api/contact-options/' . $this->contactOptions[0]->id);
        $response->assertStatus(404);
    }

    public function test_update_contact_option(): void
    {
        $payload = [
            'id' => $this->contactOptions[0]->id,
            'type' => 'website',
            'label' => 'Updated Test Label',
            'value' => 'test.com',
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/contact-options/', $payload);

        $response->assertStatus(200);
        $responseContactOption = json_decode($response->getContent())->data;
        $dbContactOption = ContactOption::query()
            ->whereKey($responseContactOption->id)
            ->first();

        $this->assertEquals(data_get($payload, 'id'), $dbContactOption->id);
        $this->assertEquals(data_get($payload, 'type'), $dbContactOption->type);
        $this->assertEquals(data_get($payload, 'label'), $dbContactOption->label);
        $this->assertEquals(data_get($payload, 'value'), $dbContactOption->value);
    }

    public function test_update_contact_option_not_found(): void
    {
        $payload = [
            'id' => $this->contactOptions[0]->id,
            'type' => 'website',
            'label' => 'Updated Test Label',
            'value' => 'test.com',
        ];

        $this->contactOptions[0]->delete();

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/contact-options/', $payload);

        $response->assertStatus(422);
    }

    public function test_update_contact_option_validation_fails(): void
    {
        $payload = [
            'id' => $this->contactOptions[0]->id,
            'type' => 42,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/contact-options/', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['type']);
    }
}
