<?php

use FluxErp\Livewire\Order\Related\Tickets;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Tickets::class)
        ->assertOk();
});
