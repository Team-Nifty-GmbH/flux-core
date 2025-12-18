<?php

use FluxErp\Livewire\Settings\TicketTypes;
use FluxErp\Models\TicketType;
use Livewire\Livewire;

test('renders successfully', function (): void {
    TicketType::factory()
        ->count(5)
        ->create();

    Livewire::test(TicketTypes::class)
        ->assertOk();
});
