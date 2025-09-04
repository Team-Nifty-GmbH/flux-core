<?php

use FluxErp\Livewire\Widgets\UnassignedTickets;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(UnassignedTickets::class)
        ->assertOk();
});
