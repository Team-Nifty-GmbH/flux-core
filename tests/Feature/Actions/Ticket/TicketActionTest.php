<?php

use FluxErp\Actions\Ticket\CreateTicket;
use FluxErp\Actions\Ticket\DeleteTicket;
use FluxErp\Actions\Ticket\UpdateTicket;
use FluxErp\Models\Ticket;
use FluxErp\Models\TicketType;

beforeEach(function (): void {
    $this->ticketType = TicketType::factory()->create();
});

test('create ticket', function (): void {
    $ticket = CreateTicket::make([
        'title' => 'Login broken',
        'description' => 'Users cannot log in',
        'ticket_type_id' => $this->ticketType->getKey(),
        'authenticatable_type' => morph_alias($this->user::class),
        'authenticatable_id' => $this->user->getKey(),
    ])->validate()->execute();

    expect($ticket)
        ->toBeInstanceOf(Ticket::class)
        ->title->toBe('Login broken')
        ->ticket_number->not->toBeNull();
});

test('create ticket auto generates ticket number', function (): void {
    $ticket = CreateTicket::make([
        'title' => 'Auto number test',
        'description' => 'Testing auto number generation',
        'ticket_type_id' => $this->ticketType->getKey(),
        'authenticatable_type' => morph_alias($this->user::class),
        'authenticatable_id' => $this->user->getKey(),
    ])->validate()->execute();

    expect($ticket->ticket_number)->not->toBeEmpty();
});

test('update ticket', function (): void {
    $ticket = Ticket::factory()->create([
        'ticket_type_id' => $this->ticketType->getKey(),
        'authenticatable_type' => morph_alias($this->user::class),
        'authenticatable_id' => $this->user->getKey(),
    ]);

    $updated = UpdateTicket::make([
        'id' => $ticket->getKey(),
        'title' => 'Updated title',
    ])->validate()->execute();

    expect($updated->title)->toBe('Updated title');
});

test('delete ticket', function (): void {
    $ticket = Ticket::factory()->create([
        'ticket_type_id' => $this->ticketType->getKey(),
        'authenticatable_type' => morph_alias($this->user::class),
        'authenticatable_id' => $this->user->getKey(),
    ]);

    $result = DeleteTicket::make(['id' => $ticket->getKey()])
        ->validate()->execute();

    expect($result)->toBeTrue();
});
