<?php

use FluxErp\Livewire\Settings\LeadStates;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(LeadStates::class)
        ->assertOk();
});
