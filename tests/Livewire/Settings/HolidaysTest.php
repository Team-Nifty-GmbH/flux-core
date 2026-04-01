<?php

use FluxErp\Livewire\Settings\Holidays;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Holidays::class)
        ->assertOk();
});
