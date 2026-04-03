<?php

use FluxErp\Livewire\Settings\VacationBlackouts;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(VacationBlackouts::class)
        ->assertOk();
});
