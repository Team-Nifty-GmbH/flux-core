<?php

use FluxErp\Livewire\Portal\Ticket\Ticket as TicketView;
use FluxErp\Models\Address;
use FluxErp\Models\Ticket;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $ticket = Ticket::factory()->create([
        'authenticatable_type' => morph_alias(Address::class),
        'authenticatable_id' => $this->address->id,
    ]);
    Livewire::test(TicketView::class, ['id' => $ticket->id])
        ->assertOk();
});
