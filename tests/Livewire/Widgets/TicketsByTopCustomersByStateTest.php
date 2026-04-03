<?php

use FluxErp\Livewire\Widgets\TicketsByTopCustomersByState;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(TicketsByTopCustomersByState::class)
        ->assertOk();
});
