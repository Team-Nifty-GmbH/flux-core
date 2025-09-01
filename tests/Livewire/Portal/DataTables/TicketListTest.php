<?php

uses(FluxErp\Tests\Livewire\PortalBaseSetup::class);
use FluxErp\Livewire\Portal\DataTables\TicketList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(TicketList::class)
        ->assertStatus(200);
});
