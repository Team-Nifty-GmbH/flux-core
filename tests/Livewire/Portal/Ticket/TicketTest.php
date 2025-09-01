<?php

uses(FluxErp\Tests\Livewire\PortalBaseSetup::class);
use FluxErp\Livewire\Portal\Ticket\Ticket as TicketView;
use FluxErp\Models\Address;
use FluxErp\Models\Ticket;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->ticket = Ticket::factory()->create([
        'authenticatable_type' => morph_alias(Address::class),
        'authenticatable_id' => $this->address->id,
    ]);
});

test('renders successfully', function (): void {
    Livewire::test(TicketView::class, ['id' => $this->ticket->id])
        ->assertStatus(200);
});
