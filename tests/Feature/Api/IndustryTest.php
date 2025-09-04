<?php

use FluxErp\Models\Client;
use FluxErp\Models\Industry;
use FluxErp\Models\Permission;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $dbClient = Client::factory()->create();

    $this->industries = Industry::factory()->count(3)->create();

    $this->user->clients()->attach($dbClient->id);

    $this->permissions = [
        'show' => Permission::findOrCreate('api.industries.{id}.get'),
        'index' => Permission::findOrCreate('api.industries.get'),
        'create' => Permission::findOrCreate('api.industries.post'),
        'update' => Permission::findOrCreate('api.industries.put'),
        'delete' => Permission::findOrCreate('api.industries.{id}.delete'),
    ];
});

test('create industry', function (): void {
    $industry = [
        'name' => 'Technology',
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->post('/api/industries', $industry);
    $response->assertCreated();

    $responseIndustry = json_decode($response->getContent())->data;
    $dbIndustry = Industry::query()
        ->whereKey($responseIndustry->id)
        ->first();

    expect($dbIndustry)->not->toBeEmpty();
    expect($dbIndustry->name)->toEqual($industry['name']);
});

test('create industry validation fails', function (): void {
    $industry = [
        'name' => '',
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->post('/api/industries', $industry);
    $response->assertUnprocessable();

    $response->assertJsonValidationErrors([
        'name',
    ]);
});

test('create industry with auto order', function (): void {
    $industry = [
        'name' => 'Healthcare',
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->post('/api/industries', $industry);
    $response->assertCreated();

    $responseIndustry = json_decode($response->getContent())->data;
    $dbIndustry = Industry::query()
        ->whereKey($responseIndustry->id)
        ->first();

    expect($dbIndustry)->not->toBeEmpty();
    expect($dbIndustry->name)->toEqual($industry['name']);
    expect($dbIndustry->order_column)->toBeInt();
});

test('delete industry', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->delete('/api/industries/' . $this->industries[0]->id);
    $response->assertNoContent();

    $industry = $this->industries[0]->fresh();
    expect($industry)->toBeNull();
});

test('delete industry not found', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->delete('/api/industries/' . (Industry::max('id') + 1));
    $response->assertNotFound();
});

test('get industries', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->get('/api/industries');
    $response->assertOk();

    $json = json_decode($response->getContent());
    $jsonIndustries = collect($json->data->data);

    expect(count($jsonIndustries))->toBeGreaterThanOrEqual(3);

    foreach ($this->industries as $industry) {
        $jsonIndustries->contains(function ($jsonIndustry) use ($industry) {
            return $jsonIndustry->id === $industry->id &&
                $jsonIndustry->name === $industry->name;
        });
    }
});

test('get industry', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->get('/api/industries/' . $this->industries[0]->id);
    $response->assertOk();

    $json = json_decode($response->getContent());
    $jsonIndustry = $json->data;

    expect($jsonIndustry)->not->toBeEmpty();
    expect($jsonIndustry->id)->toEqual($this->industries[0]->id);
    expect($jsonIndustry->name)->toEqual($this->industries[0]->name);
});

test('get industry not found', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->get('/api/industries/' . (Industry::max('id') + 1));
    $response->assertNotFound();
});

test('update industry', function (): void {
    $industry = [
        'id' => $this->industries[0]->id,
        'name' => 'Updated Industry Name',
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->put('/api/industries', $industry);
    $response->assertOk();

    $responseIndustry = json_decode($response->getContent())->data;
    $dbIndustry = Industry::query()
        ->whereKey($responseIndustry->id)
        ->first();

    expect($dbIndustry)->not->toBeEmpty();
    expect($dbIndustry->id)->toEqual($industry['id']);
    expect($dbIndustry->name)->toEqual($industry['name']);
});

test('update industry maximum', function (): void {
    $industry = [
        'id' => $this->industries[1]->id,
        'name' => 'Fully Updated Industry',
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->put('/api/industries', $industry);
    $response->assertOk();

    $responseIndustry = json_decode($response->getContent())->data;
    $dbIndustry = Industry::query()
        ->whereKey($responseIndustry->id)
        ->first();

    expect($dbIndustry)->not->toBeEmpty();
    expect($dbIndustry->id)->toEqual($industry['id']);
    expect($dbIndustry->name)->toEqual($industry['name']);
    expect($dbIndustry->order_column)->toBeInt();
});

test('update industry validation fails', function (): void {
    $industry = [
        'id' => $this->industries[0]->id,
        'name' => '',
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->put('/api/industries', $industry);
    $response->assertUnprocessable();

    $response->assertJsonValidationErrors([
        'name',
    ]);
});
