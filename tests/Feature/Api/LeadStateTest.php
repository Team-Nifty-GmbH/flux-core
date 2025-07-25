<?php

namespace FluxErp\Tests\Feature\Api;

use FluxErp\Models\Client;
use FluxErp\Models\LeadState;
use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Support\Collection;
use Laravel\Sanctum\Sanctum;

class LeadStateTest extends BaseSetup
{
    private Collection $leadStates;

    private array $permissions;

    protected function setUp(): void
    {
        parent::setUp();

        $dbClient = Client::factory()->create();

        $this->leadStates = LeadState::factory()->count(3)->create([
            'is_default' => false,
        ]);

        $this->leadStates->push(
            LeadState::factory()->create([
                'is_default' => true,
            ])
        );

        $this->user->clients()->attach($dbClient->id);

        $this->permissions = [
            'show' => Permission::findOrCreate('api.lead-states.{id}.get'),
            'index' => Permission::findOrCreate('api.lead-states.get'),
            'create' => Permission::findOrCreate('api.lead-states.post'),
            'update' => Permission::findOrCreate('api.lead-states.put'),
            'delete' => Permission::findOrCreate('api.lead-states.{id}.delete'),
        ];
    }

    public function test_create_lead_state(): void
    {
        $leadState = [
            'name' => 'New Lead',
            'color' => '#FF5733',
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->post('/api/lead-states', $leadState);
        $response->assertStatus(201);

        $responseLeadState = json_decode($response->getContent())->data;
        $dbLeadState = LeadState::query()
            ->whereKey($responseLeadState->id)
            ->first();

        $this->assertNotEmpty($dbLeadState);
        $this->assertEquals($leadState['name'], $dbLeadState->name);
        $this->assertEquals($leadState['color'], $dbLeadState->color);
        $this->assertFalse($dbLeadState->is_default);
        $this->assertFalse($dbLeadState->is_won);
        $this->assertFalse($dbLeadState->is_lost);
        $this->assertTrue($this->user->is($dbLeadState->getCreatedBy()));
        $this->assertTrue($this->user->is($dbLeadState->getUpdatedBy()));
    }

    public function test_create_lead_state_maximum(): void
    {
        $leadState = [
            'name' => 'Won Lead',
            'color' => '#28A745',
            'is_default' => false,
            'is_won' => true,
            'is_lost' => false,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->post('/api/lead-states', $leadState);
        $response->assertStatus(201);

        $responseLeadState = json_decode($response->getContent())->data;
        $dbLeadState = LeadState::query()
            ->whereKey($responseLeadState->id)
            ->first();

        $this->assertNotEmpty($dbLeadState);
        $this->assertEquals($leadState['name'], $dbLeadState->name);
        $this->assertEquals($leadState['color'], $dbLeadState->color);
        $this->assertEquals($leadState['is_default'], $dbLeadState->is_default);
        $this->assertEquals($leadState['is_won'], $dbLeadState->is_won);
        $this->assertEquals($leadState['is_lost'], $dbLeadState->is_lost);
    }

    public function test_create_lead_state_validation_fails(): void
    {
        $leadState = [
            'name' => '',
            'color' => 'invalid-color',
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->post('/api/lead-states', $leadState);
        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'name',
        ]);
    }

    public function test_delete_lead_state(): void
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->delete('/api/lead-states/' . $this->leadStates[0]->id);
        $response->assertStatus(204);

        $leadState = $this->leadStates[0]->fresh();
        $this->assertNotNull($leadState->deleted_at);
        $this->assertTrue($this->user->is($leadState->getDeletedBy()));
    }

    public function test_delete_lead_state_not_found(): void
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->delete('/api/lead-states/' . (LeadState::max('id') + 1));
        $response->assertStatus(404);
    }

    public function test_get_lead_state(): void
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->get('/api/lead-states/' . $this->leadStates[0]->id);
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $jsonLeadState = $json->data;

        $this->assertNotEmpty($jsonLeadState);
        $this->assertEquals($this->leadStates[0]->id, $jsonLeadState->id);
        $this->assertEquals($this->leadStates[0]->name, $jsonLeadState->name);
        $this->assertEquals($this->leadStates[0]->color, $jsonLeadState->color);
        $this->assertEquals($this->leadStates[0]->fresh()->is_default, $jsonLeadState->is_default);
        $this->assertEquals((bool) $this->leadStates[0]->is_won, (bool) $jsonLeadState->is_won);
        $this->assertEquals((bool) $this->leadStates[0]->is_lost, (bool) $jsonLeadState->is_lost);
        $this->assertNotNull($jsonLeadState->image);
    }

    public function test_get_lead_state_not_found(): void
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->get('/api/lead-states/' . (LeadState::max('id') + 1));
        $response->assertStatus(404);
    }

    public function test_get_lead_states(): void
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->get('/api/lead-states');
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $jsonLeadStates = collect($json->data->data);

        $this->assertGreaterThanOrEqual(4, count($jsonLeadStates));

        foreach ($this->leadStates as $leadState) {
            $jsonLeadStates->contains(function ($jsonLeadState) use ($leadState) {
                return $jsonLeadState->id === $leadState->id &&
                    $jsonLeadState->name === $leadState->name &&
                    $jsonLeadState->color === $leadState->color &&
                    $jsonLeadState->is_default === $leadState->is_default &&
                    $jsonLeadState->is_won === $leadState->is_won &&
                    $jsonLeadState->is_lost === $leadState->is_lost;
            });
        }
    }

    public function test_lead_state_default_functionality(): void
    {
        $defaultLeadState = $this->leadStates->where('is_default', true)->first();
        $this->assertNotNull($defaultLeadState);

        $defaultCount = LeadState::query()
            ->where('is_default', true)
            ->count();

        $this->assertEquals(1, $defaultCount);
    }

    public function test_lead_state_image_attribute(): void
    {
        $leadState = LeadState::factory()->create([
            'name' => 'Test Lead State',
            'color' => '#FF5733',
        ]);

        $image = $leadState->image;
        $this->assertIsString($image);
        $this->assertStringContainsString('avatar', $image);
        $this->assertStringContainsString('FF5733', $image);
    }

    public function test_update_lead_state(): void
    {
        $leadState = [
            'id' => $this->leadStates[0]->id,
            'name' => 'Updated Lead State',
            'color' => '#6C757D',
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->put('/api/lead-states', $leadState);
        $response->assertStatus(200);

        $responseLeadState = json_decode($response->getContent())->data;
        $dbLeadState = LeadState::query()
            ->whereKey($responseLeadState->id)
            ->first();

        $this->assertNotEmpty($dbLeadState);
        $this->assertEquals($leadState['id'], $dbLeadState->id);
        $this->assertEquals($leadState['name'], $dbLeadState->name);
        $this->assertEquals($leadState['color'], $dbLeadState->color);
        $this->assertTrue($this->user->is($dbLeadState->getUpdatedBy()));
    }

    public function test_update_lead_state_maximum(): void
    {
        $leadState = [
            'id' => $this->leadStates[1]->id,
            'name' => 'Lost Lead State',
            'color' => '#DC3545',
            'is_default' => false,
            'is_won' => false,
            'is_lost' => true,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->put('/api/lead-states', $leadState);
        $response->assertStatus(200);

        $responseLeadState = json_decode($response->getContent())->data;
        $dbLeadState = LeadState::query()
            ->whereKey($responseLeadState->id)
            ->first();

        $this->assertNotEmpty($dbLeadState);
        $this->assertEquals($leadState['id'], $dbLeadState->id);
        $this->assertEquals($leadState['name'], $dbLeadState->name);
        $this->assertEquals($leadState['color'], $dbLeadState->color);
        $this->assertEquals($leadState['is_default'], $dbLeadState->is_default);
        $this->assertEquals($leadState['is_won'], $dbLeadState->is_won);
        $this->assertEquals($leadState['is_lost'], $dbLeadState->is_lost);
        $this->assertTrue($this->user->is($dbLeadState->getUpdatedBy()));
    }

    public function test_update_lead_state_validation_fails(): void
    {
        $leadState = [
            'id' => $this->leadStates[0]->id,
            'name' => '',
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->put('/api/lead-states', $leadState);
        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'name',
        ]);
    }
}
