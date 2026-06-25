<?php

use FluxErp\Models\Contact;
use FluxErp\Models\Permission;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->searchPermission = Permission::findOrCreate('api.search.{model}.get', 'sanctum');
});

test('the search endpoint returns formatted records for a morph alias', function (): void {
    $contacts = Contact::factory()->count(2)->create();
    $this->user->givePermissionTo($this->searchPermission);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->getJson('/api/search/contact?selected[]=' . $contacts[0]->getKey())
        ->assertOk();

    expect($response->json())->toBeArray();
    expect($response->json('0.id'))->toBe($contacts[0]->getKey());
    expect($response->json('0'))->toHaveKeys(['id', 'label', 'description', 'image']);
});

test('the search endpoint denies a user without the route permission', function (): void {
    Sanctum::actingAs($this->user, ['user']);

    $this->getJson('/api/search/contact?selected[]=1')->assertStatus(403);
});

test('the search endpoint 404s for an unknown model', function (): void {
    $this->user->givePermissionTo($this->searchPermission);
    Sanctum::actingAs($this->user, ['user']);

    $this->getJson('/api/search/not-a-model?search=x')->assertStatus(404);
});
