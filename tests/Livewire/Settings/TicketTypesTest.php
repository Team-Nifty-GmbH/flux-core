<?php

use FluxErp\Livewire\Settings\TicketTypes;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\TicketType;
use Livewire\Livewire;

beforeEach(function (): void {
    TicketType::factory()
        ->has(AdditionalColumn::factory()->count(3))
        ->count(5)
        ->create();
});

test('renders successfully', function (): void {
    Livewire::test(TicketTypes::class)
        ->assertStatus(200);
});
