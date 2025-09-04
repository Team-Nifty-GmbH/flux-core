<?php

use FluxErp\Livewire\Settings\Units;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Units::class)
        ->assertOk();
});
