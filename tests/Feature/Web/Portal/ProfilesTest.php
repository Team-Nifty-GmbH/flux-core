<?php

use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Permission;

test('portal profiles address not found', function (): void {
    $contact = Contact::factory()->create([
        'client_id' => $this->dbClient->getKey(),
    ]);

    $address = Address::factory()->create([
        'contact_id' => $contact->id,
        'client_id' => $this->dbClient->getKey(),
        'language_id' => $this->address->language_id,
    ]);

    $this->address->givePermissionTo(Permission::findOrCreate('profiles.{id?}.get', 'address'));

    $this->actingAs($this->address, 'address')->get(
        route('portal.profiles.id?', ['id' => $address->id])
    )
        ->assertNotFound();
});

test('portal profiles new profile', function (): void {
    $this->address->givePermissionTo(Permission::findOrCreate('profiles.{id?}.get', 'address'));

    $this->actingAs($this->address, 'address')->get(route('portal.profiles.id?', ['id' => 'new']))
        ->assertOk();
});

test('portal profiles no user', function (): void {
    $this->actingAsGuest();

    $this->get(route('portal.profiles.id?', ['id' => $this->address->id]))
        ->assertFound()
        ->assertRedirect(config('flux.portal_domain') . '/login');
});

test('portal profiles page', function (): void {
    $this->address->givePermissionTo(Permission::findOrCreate('profiles.{id?}.get', 'address'));

    $this->actingAs($this->address, 'address')->get(
        route('portal.profiles.id?', ['id' => $this->address->id])
    )
        ->assertOk();
});

test('portal profiles without id', function (): void {
    $this->address->givePermissionTo(Permission::findOrCreate('profiles.{id?}.get', 'address'));

    $this->actingAs($this->address, 'address')->get(route('portal.profiles.id?', ['id' => null]))
        ->assertOk();
});

test('portal profiles without permission', function (): void {
    Permission::findOrCreate('profiles.{id?}.get', 'address');

    $this->actingAs($this->address, 'address')->get(
        route('portal.profiles.id?', ['id' => $this->address->id])
    )
        ->assertForbidden();
});
