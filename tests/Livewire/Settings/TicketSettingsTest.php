<?php

use FluxErp\Livewire\Settings\TicketSettings;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(TicketSettings::class)
        ->assertOk();
});
