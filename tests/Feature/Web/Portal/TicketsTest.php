<?php

uses(FluxErp\Tests\Feature\Web\Portal\PortalSetup::class);
use FluxErp\Models\Address;
use FluxErp\Models\Permission;
use FluxErp\Models\Ticket;

beforeEach(function (): void {
    $this->ticket = Ticket::factory()->create([
        'authenticatable_type' => morph_alias(Address::class),
        'authenticatable_id' => $this->user->id,
    ]);
});

test('portal tickets id no user', function (): void {
    $this->get(route('portal.tickets.id', ['id' => $this->ticket->id]))
        ->assertStatus(302)
        ->assertRedirect($this->portalDomain . '/login');
});

test('portal tickets id page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('tickets.{id}.get', 'address'));

    $this->actingAs($this->user, 'address')->get(route('portal.tickets.id', ['id' => $this->ticket->id]))
        ->assertStatus(200);
});

test('portal tickets id ticket not found', function (): void {
    $this->ticket->delete();

    $this->user->givePermissionTo(Permission::findOrCreate('tickets.{id}.get', 'address'));

    $this->actingAs($this->user, 'address')->get(route('portal.tickets.id', ['id' => $this->ticket->id]))
        ->assertStatus(404);
});

test('portal tickets id without permission', function (): void {
    Permission::findOrCreate('tickets.{id}.get', 'address');

    $this->actingAs($this->user, 'address')->get(route('portal.tickets.id', ['id' => $this->ticket->id]))
        ->assertStatus(403);
});

test('portal tickets no user', function (): void {
    $this->get(route('portal.tickets'))
        ->assertStatus(302)
        ->assertRedirect($this->portalDomain . '/login');
});

test('portal tickets page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('tickets.get', 'address'));

    $this->actingAs($this->user, 'address')->get(route('portal.tickets'))
        ->assertStatus(200);
});

test('portal tickets without permission', function (): void {
    Permission::findOrCreate('tickets.get', 'address');

    $this->actingAs($this->user, 'address')->get(route('portal.tickets'))
        ->assertStatus(403);
});
