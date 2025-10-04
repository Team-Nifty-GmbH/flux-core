<?php

use FluxErp\Livewire\DataTables\TicketList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(TicketList::class)
        ->assertOk();
});
