<?php

uses(FluxErp\Tests\Feature\Web\Portal\PortalSetup::class);
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
        'language_id' => $this->user->language_id,
    ]);

    $this->user->givePermissionTo(Permission::findOrCreate('profiles.{id?}.get', 'address'));

    $this->actingAs($this->user, 'address')->get(
        route('portal.profiles.id?', ['id' => $address->id])
    )
        ->assertStatus(404);
});

test('portal profiles new profile', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('profiles.{id?}.get', 'address'));

    $this->actingAs($this->user, 'address')->get(route('portal.profiles.id?', ['id' => 'new']))
        ->assertStatus(200);
});

test('portal profiles no user', function (): void {
    $this->get(route('portal.profiles.id?', ['id' => $this->user->id]))
        ->assertStatus(302)
        ->assertRedirect($this->portalDomain . '/login');
});

test('portal profiles page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('profiles.{id?}.get', 'address'));

    $this->actingAs($this->user, 'address')->get(
        route('portal.profiles.id?', ['id' => $this->user->id])
    )
        ->assertStatus(200);
});

test('portal profiles without id', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('profiles.{id?}.get', 'address'));

    $this->actingAs($this->user, 'address')->get(route('portal.profiles.id?', ['id' => null]))
        ->assertStatus(200);
});

test('portal profiles without permission', function (): void {
    Permission::findOrCreate('profiles.{id?}.get', 'address');

    $this->actingAs($this->user, 'address')->get(
        route('portal.profiles.id?', ['id' => $this->user->id])
    )
        ->assertStatus(403);
});
