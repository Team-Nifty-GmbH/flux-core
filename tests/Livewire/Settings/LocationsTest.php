<?php

use FluxErp\Livewire\Settings\Locations;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Locations::class)
        ->assertOk();
});
