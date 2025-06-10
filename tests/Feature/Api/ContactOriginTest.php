<?php

namespace FluxErp\Tests\Feature\Api;

use FluxErp\Models\ContactOrigin;
use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;

class ContactOriginTest extends BaseSetup
{
    use WithFaker;

    private $origins;

    private $permissions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->origins = ContactOrigin::factory()->count(2)->create();

        $this->permissions = [
            'index' => Permission::findOrCreate('api.contact-origins.get'),
            'show' => Permission::findOrCreate('api.contact-origins.{id}.get'),
            'create' => Permission::findOrCreate('api.contact-origins.post'),
            'update' => Permission::findOrCreate('api.contact-origins.put'),
            'delete' => Permission::findOrCreate('api.contact-origins.{id}.delete'),
        ];
    }

    public function test_create_contact_origin_defaults(): void
    {
        $payload = [
            'name' => 'Testname',
            'is_active' => true,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/contact-origins', $payload);
        $response->assertStatus(201);

        $data = json_decode($response->getContent())->data;

        $db = ContactOrigin::query()
            ->whereKey($data->id)
            ->first();

        $this->assertNotNull($db);
        $this->assertEquals($payload['name'], $db->name);
        $this->assertTrue($db->is_active);
    }

    public function test_create_validation_fails(): void
    {
        $payload = [
            'is_active' => true,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/contact-origins', $payload);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_delete_contact_origin(): void
    {
        $origin = $this->origins->last();

        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $this->actingAs($this->user)->delete('/api/contact-origins/' . $origin->getKey())
            ->assertStatus(204);

        $this->assertNull(
            ContactOrigin::query()
                ->whereKey($origin->id)
                ->first()
        );
    }

    public function test_delete_non_existent_contact_origin(): void
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $this->actingAs($this->user)->delete('/api/contact-origins/' . $this->origins->last()->getKey() + 1)
            ->assertStatus(404);
    }

    public function test_get_contact_origin(): void
    {
        $origin = $this->origins->first();

        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/contact-origins/' . $origin->getKey());
        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertEquals($origin->id, $data['id']);
        $this->assertEquals($origin->name, $data['name']);
        $this->assertEquals($origin->is_active, $data['is_active']);
    }

    public function test_get_non_existent_contact_origin(): void
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)
            ->get('/api/contact-origins/' . ($this->origins->last()->getKey() + 1));
        $response->assertStatus(404);
    }

    public function test_index_contact_origins(): void
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/contact-origins');
        $response->assertStatus(200);

        $items = collect($response->json('data.data'));
        $this->assertGreaterThanOrEqual(
            $this->origins->count(),
            $items->count()
        );

        foreach ($this->origins as $origin) {
            $this->assertTrue(
                $items->contains(fn ($i) => $i['id'] === $origin->id
                    && $i['name'] === $origin->name
                    && $i['is_active'] === $origin->is_active
                )
            );
        }
    }

    public function test_update_contact_origin(): void
    {
        $origin = $this->origins->first();
        $payload = [
            'id' => $origin->id,
            'name' => 'New Name',
            'is_active' => false,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/contact-origins', $payload);
        $response->assertStatus(200);

        $data = $response->json('data');
        $db = ContactOrigin::query()
            ->whereKey($data['id'])
            ->first();

        $this->assertEquals($payload['name'], $db->name);
        $this->assertFalse($db->is_active);
    }

    public function test_update_validation_fails(): void
    {
        $payload = [
            'name' => 'New Name',
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $this->actingAs($this->user)
            ->put('/api/contact-origins', $payload)
            ->assertStatus(422);
    }
}
