<?php

use FluxErp\Livewire\Settings\VacationCarryoverRules;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(VacationCarryoverRules::class)
        ->assertOk();
});
