<?php

use FluxErp\Livewire\Settings\AbsenceTypes;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(AbsenceTypes::class)
        ->assertOk();
});
