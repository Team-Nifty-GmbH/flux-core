<?php

use FluxErp\Models\LeadState;
use FluxErp\Models\Permission;
use FluxErp\Models\Tenant;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $dbTenant = Tenant::factory()->create();

    $this->leadStates = LeadState::factory()->count(3)->create([
        'is_default' => false,
    ]);

    $this->leadStates->push(
        LeadState::factory()->create([
            'is_default' => true,
        ])
    );

    $this->user->tenants()->attach($dbTenant->id);

    $this->permissions = [
        'show' => Permission::findOrCreate('api.lead-states.{id}.get'),
        'index' => Permission::findOrCreate('api.lead-states.get'),
        'create' => Permission::findOrCreate('api.lead-states.post'),
        'update' => Permission::findOrCreate('api.lead-states.put'),
        'delete' => Permission::findOrCreate('api.lead-states.{id}.delete'),
    ];
});

test('create lead state', function (): void {
    $leadState = [
        'name' => 'New Lead',
        'color' => '#FF5733',
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->post('/api/lead-states', $leadState);
    $response->assertCreated();

    $responseLeadState = json_decode($response->getContent())->data;
    $dbLeadState = LeadState::query()
        ->whereKey($responseLeadState->id)
        ->first();

    expect($dbLeadState)->not->toBeEmpty();
    expect($dbLeadState->name)->toEqual($leadState['name']);
    expect($dbLeadState->color)->toEqual($leadState['color']);
    expect($dbLeadState->is_default)->toBeFalse();
    expect($dbLeadState->is_won)->toBeFalse();
    expect($dbLeadState->is_lost)->toBeFalse();
    expect($this->user->is($dbLeadState->getCreatedBy()))->toBeTrue();
    expect($this->user->is($dbLeadState->getUpdatedBy()))->toBeTrue();
});

test('create lead state maximum', function (): void {
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
    $response->assertCreated();

    $responseLeadState = json_decode($response->getContent())->data;
    $dbLeadState = LeadState::query()
        ->whereKey($responseLeadState->id)
        ->first();

    expect($dbLeadState)->not->toBeEmpty();
    expect($dbLeadState->name)->toEqual($leadState['name']);
    expect($dbLeadState->color)->toEqual($leadState['color']);
    expect($dbLeadState->is_default)->toEqual($leadState['is_default']);
    expect($dbLeadState->is_won)->toEqual($leadState['is_won']);
    expect($dbLeadState->is_lost)->toEqual($leadState['is_lost']);
});

test('create lead state validation fails', function (): void {
    $leadState = [
        'name' => '',
        'color' => 'invalid-color',
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->post('/api/lead-states', $leadState);
    $response->assertUnprocessable();

    $response->assertJsonValidationErrors([
        'name',
    ]);
});

test('delete lead state', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->delete('/api/lead-states/' . $this->leadStates[0]->id);
    $response->assertNoContent();

    $leadState = $this->leadStates[0]->fresh();
    expect($leadState->deleted_at)->not->toBeNull();
    expect($this->user->is($leadState->getDeletedBy()))->toBeTrue();
});

test('delete lead state not found', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->delete('/api/lead-states/' . (LeadState::max('id') + 1));
    $response->assertNotFound();
});

test('get lead state', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->get('/api/lead-states/' . $this->leadStates[0]->id);
    $response->assertOk();

    $json = json_decode($response->getContent());
    $jsonLeadState = $json->data;

    expect($jsonLeadState)->not->toBeEmpty();
    expect($jsonLeadState->id)->toEqual($this->leadStates[0]->id);
    expect($jsonLeadState->name)->toEqual($this->leadStates[0]->name);
    expect($jsonLeadState->color)->toEqual($this->leadStates[0]->color);
    expect($jsonLeadState->is_default)->toEqual($this->leadStates[0]->fresh()->is_default);
    expect((bool) $jsonLeadState->is_won)->toEqual((bool) $this->leadStates[0]->is_won);
    expect((bool) $jsonLeadState->is_lost)->toEqual((bool) $this->leadStates[0]->is_lost);
    expect($jsonLeadState->image)->not->toBeNull();
});

test('get lead state not found', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->get('/api/lead-states/' . (LeadState::max('id') + 1));
    $response->assertNotFound();
});

test('get lead states', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->get('/api/lead-states');
    $response->assertOk();

    $json = json_decode($response->getContent());
    $jsonLeadStates = collect($json->data->data);

    expect(count($jsonLeadStates))->toBeGreaterThanOrEqual(4);

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
});

test('lead state default functionality', function (): void {
    $defaultLeadState = $this->leadStates->where('is_default', true)->first();
    expect($defaultLeadState)->not->toBeNull();

    $defaultCount = LeadState::query()
        ->where('is_default', true)
        ->count();

    expect($defaultCount)->toEqual(1);
});

test('lead state image attribute', function (): void {
    $leadState = LeadState::factory()->create([
        'name' => 'Test Lead State',
        'color' => '#FF5733',
    ]);

    $image = $leadState->image;
    expect($image)->toBeString();
    $this->assertStringContainsString('avatar', $image);
    $this->assertStringContainsString('FF5733', $image);
});

test('update lead state', function (): void {
    $leadState = [
        'id' => $this->leadStates[0]->id,
        'name' => 'Updated Lead State',
        'color' => '#6C757D',
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->put('/api/lead-states', $leadState);
    $response->assertOk();

    $responseLeadState = json_decode($response->getContent())->data;
    $dbLeadState = LeadState::query()
        ->whereKey($responseLeadState->id)
        ->first();

    expect($dbLeadState)->not->toBeEmpty();
    expect($dbLeadState->id)->toEqual($leadState['id']);
    expect($dbLeadState->name)->toEqual($leadState['name']);
    expect($dbLeadState->color)->toEqual($leadState['color']);
    expect($this->user->is($dbLeadState->getUpdatedBy()))->toBeTrue();
});

test('update lead state maximum', function (): void {
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
    $response->assertOk();

    $responseLeadState = json_decode($response->getContent())->data;
    $dbLeadState = LeadState::query()
        ->whereKey($responseLeadState->id)
        ->first();

    expect($dbLeadState)->not->toBeEmpty();
    expect($dbLeadState->id)->toEqual($leadState['id']);
    expect($dbLeadState->name)->toEqual($leadState['name']);
    expect($dbLeadState->color)->toEqual($leadState['color']);
    expect($dbLeadState->is_default)->toEqual($leadState['is_default']);
    expect($dbLeadState->is_won)->toEqual($leadState['is_won']);
    expect($dbLeadState->is_lost)->toEqual($leadState['is_lost']);
    expect($this->user->is($dbLeadState->getUpdatedBy()))->toBeTrue();
});

test('update lead state validation fails', function (): void {
    $leadState = [
        'id' => $this->leadStates[0]->id,
        'name' => '',
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->put('/api/lead-states', $leadState);
    $response->assertUnprocessable();

    $response->assertJsonValidationErrors([
        'name',
    ]);
});
