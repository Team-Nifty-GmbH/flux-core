<?php

use FluxErp\Livewire\Settings\TicketTypes;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\TicketType;
use Livewire\Livewire;

test('renders successfully', function (): void {
    TicketType::factory()
        ->has(AdditionalColumn::factory()->count(3))
        ->count(5)
        ->create();

    Livewire::test(TicketTypes::class)
        ->assertOk();
});
