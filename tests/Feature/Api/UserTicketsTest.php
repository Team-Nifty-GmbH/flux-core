<?php

use FluxErp\Models\Permission;
use FluxErp\Models\Ticket;
use FluxErp\States\Ticket\Closed as TicketClosed;
use FluxErp\States\Ticket\InProgress as TicketInProgress;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->ticketsPermission = Permission::findOrCreate('api.user.tickets.get', 'sanctum');
});

test('the user tickets endpoint returns open tickets assigned to the user', function (): void {
    $authenticatable = [
        'authenticatable_type' => $this->user->getMorphClass(),
        'authenticatable_id' => $this->user->getKey(),
    ];
    $mine = Ticket::factory()->create(['state' => TicketInProgress::class, ...$authenticatable]);
    $mine->users()->attach($this->user->id);
    $closedMine = Ticket::factory()->create(['state' => TicketClosed::class, ...$authenticatable]);
    $closedMine->users()->attach($this->user->id);
    $someoneElse = Ticket::factory()->create(['state' => TicketInProgress::class, ...$authenticatable]);

    $this->user->givePermissionTo($this->ticketsPermission);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->getJson('/api/user/tickets')->assertOk();

    $ids = collect($response->json('data'))->pluck('id');
    expect($ids)->toContain($mine->id);
    expect($ids)->not->toContain($closedMine->id, $someoneElse->id);
    expect($response->json('data.0'))->toHaveKeys(['id', 'ticket_number', 'title', 'state', 'url']);
});
