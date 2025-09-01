<?php

uses(FluxErp\Tests\Feature\Web\BaseSetup::class);
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Permission;

beforeEach(function (): void {
    $paymentType = PaymentType::factory()
        ->hasAttached(factory: $this->dbClient, relationship: 'clients')
        ->create([
            'is_default' => false,
        ]);

    $this->contact = Contact::factory()->create([
        'client_id' => $this->dbClient->getKey(),
        'payment_type_id' => $paymentType->id,
    ]);

    Address::factory()->create([
        'client_id' => $this->dbClient->getKey(),
        'contact_id' => $this->contact->id,
        'is_main_address' => true,
    ]);
});

test('contacts id contact not found', function (): void {
    $this->contact->delete();

    $this->user->givePermissionTo(Permission::findOrCreate('contacts.{id?}.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/contacts/' . $this->contact->id)
        ->assertStatus(404);
});

test('contacts id no user', function (): void {
    $this->get('/contacts/contacts/' . $this->contact->id)
        ->assertStatus(302)
        ->assertRedirect(route('login'));
});

test('contacts id page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('contacts.{id?}.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/contacts/contacts/' . $this->contact->id)
        ->assertStatus(200);
});

test('contacts id page without id', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('contacts.{id?}.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/contacts/contacts/0')
        ->assertStatus(404);
});

test('contacts id without permission', function (): void {
    Permission::findOrCreate('contacts.{id?}.get', 'web');

    $this->actingAs($this->user, 'web')->get('/contacts/contacts/' . $this->contact->id)
        ->assertStatus(403);
});

test('contacts no user', function (): void {
    $this->get('/contacts/contacts')
        ->assertStatus(302)
        ->assertRedirect(route('login'));
});

test('contacts page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('contacts.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/contacts/contacts')
        ->assertStatus(200);
});

test('contacts without permission', function (): void {
    Permission::findOrCreate('contacts.get', 'web');

    $this->actingAs($this->user, 'web')->get('/contacts/contacts')
        ->assertStatus(403);
});
