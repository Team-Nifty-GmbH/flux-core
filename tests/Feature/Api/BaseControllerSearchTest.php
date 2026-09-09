<?php

use FluxErp\Models\Contact;
use FluxErp\Models\Language;
use FluxErp\Models\Permission;
use Laravel\Sanctum\Sanctum;

test('the index endpoint accepts a search on a searchable model', function (): void {
    Contact::factory()->create();
    $this->user->givePermissionTo(Permission::findOrCreate('api.contacts.get'));
    Sanctum::actingAs($this->user, ['user']);

    $this->getJson('/api/contacts?search=a')->assertOk();
});

test('the index endpoint refuses a search on a model without scout', function (): void {
    Language::factory()->create();
    $this->user->givePermissionTo(Permission::findOrCreate('api.languages.get'));
    Sanctum::actingAs($this->user, ['user']);

    $this->getJson('/api/languages?search=a')
        ->assertStatus(400)
        ->assertJsonPath('errors.search', 'Search not allowed on given model.');
});
