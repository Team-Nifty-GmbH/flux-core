<?php

use FluxErp\Livewire\DataTables\TicketTypeList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(TicketTypeList::class)
        ->assertOk();
});
