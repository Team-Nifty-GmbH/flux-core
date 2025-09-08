<?php

use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Permission;
use FluxErp\Models\Ticket;

beforeEach(function (): void {
    $dbContact = Contact::factory()->create([
        'client_id' => $this->dbClient->getKey(),
    ]);

    $address = Address::factory()->create([
        'client_id' => $dbContact->client_id,
        'language_id' => $this->user->language_id,
        'contact_id' => $dbContact->id,
        'is_main_address' => true,
    ]);

    $this->ticket = Ticket::factory()->create([
        'authenticatable_type' => morph_alias(Address::class),
        'authenticatable_id' => $address->id,
    ]);
});

test('tickets id no user', function (): void {
    $this->actingAsGuest();

    $this->get('/tickets/' . $this->ticket->id)
        ->assertFound()
        ->assertRedirect(route('login'));
});

test('tickets id page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('tickets.{id}.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/tickets/' . $this->ticket->id)
        ->assertOk();
});

test('tickets id ticket not found', function (): void {
    $this->ticket->delete();

    $this->user->givePermissionTo(Permission::findOrCreate('tickets.{id}.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/tickets/' . $this->ticket->id)
        ->assertNotFound();
});

test('tickets id without permission', function (): void {
    Permission::findOrCreate('tickets.{id}.get', 'web');

    $this->actingAs($this->user, 'web')->get('/tickets/' . $this->ticket->id)
        ->assertForbidden();
});

test('tickets no user', function (): void {
    $this->actingAsGuest();

    $this->get('/tickets')
        ->assertFound()
        ->assertRedirect(route('login'));
});

test('tickets page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('tickets.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/tickets')
        ->assertOk();
});

test('tickets without permission', function (): void {
    Permission::findOrCreate('tickets.get', 'web');

    $this->actingAs($this->user, 'web')->get('/tickets')
        ->assertForbidden();
});
