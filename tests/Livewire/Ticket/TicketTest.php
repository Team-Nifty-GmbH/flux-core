<?php

use FluxErp\Livewire\Ticket\Ticket as TicketView;
use FluxErp\Models\Ticket;
use FluxErp\Models\User;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->ticket = Ticket::factory()->create([
        'authenticatable_type' => morph_alias(User::class),
        'authenticatable_id' => $this->user->id,
    ]);
});

test('renders successfully', function (): void {
    Livewire::test(TicketView::class, ['id' => $this->ticket->id])
        ->assertOk();
});

test('switch tabs', function (): void {
    Livewire::test(TicketView::class, ['id' => $this->ticket->id])->cycleTabs();
});
