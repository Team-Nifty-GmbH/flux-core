<?php

uses(FluxErp\Tests\Livewire\PortalBaseSetup::class);
use FluxErp\Livewire\Portal\Ticket\TicketCreate;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(TicketCreate::class)
        ->assertStatus(200);
});
