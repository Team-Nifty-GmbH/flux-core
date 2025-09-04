<?php

use FluxErp\Livewire\Settings\Countries;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Countries::class)
        ->assertOk();
});
