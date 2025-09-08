<?php

use FluxErp\Models\Address;
use FluxErp\Models\Permission;
use FluxErp\Models\Ticket;

beforeEach(function (): void {
    $this->ticket = Ticket::factory()->create([
        'authenticatable_type' => morph_alias(Address::class),
        'authenticatable_id' => $this->address->id,
    ]);
});

test('portal tickets id no user', function (): void {
    $this->actingAsGuest();

    $this->get(route('portal.tickets.id', ['id' => $this->ticket->id]))
        ->assertFound()
        ->assertRedirect(config('flux.portal_domain') . '/login');
});

test('portal tickets id page', function (): void {
    $this->address->givePermissionTo(Permission::findOrCreate('tickets.{id}.get', 'address'));

    $this->actingAs($this->address, 'address')->get(route('portal.tickets.id', ['id' => $this->ticket->id]))
        ->assertOk();
});

test('portal tickets id ticket not found', function (): void {
    $this->ticket->delete();

    $this->address->givePermissionTo(Permission::findOrCreate('tickets.{id}.get', 'address'));

    $this->actingAs($this->address, 'address')->get(route('portal.tickets.id', ['id' => $this->ticket->id]))
        ->assertNotFound();
});

test('portal tickets id without permission', function (): void {
    Permission::findOrCreate('tickets.{id}.get', 'address');

    $this->actingAs($this->address, 'address')->get(route('portal.tickets.id', ['id' => $this->ticket->id]))
        ->assertForbidden();
});

test('portal tickets no user', function (): void {
    $this->actingAsGuest();

    $this->get(route('portal.tickets'))
        ->assertFound()
        ->assertRedirect(config('flux.portal_domain') . '/login');
});

test('portal tickets page', function (): void {
    $this->address->givePermissionTo(Permission::findOrCreate('tickets.get', 'address'));

    $this->actingAs($this->address, 'address')->get(route('portal.tickets'))
        ->assertOk();
});

test('portal tickets without permission', function (): void {
    Permission::findOrCreate('tickets.get', 'address');

    $this->actingAs($this->address, 'address')->get(route('portal.tickets'))
        ->assertForbidden();
});
