<?php

use FluxErp\Livewire\Widgets\OpenDeliveries;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(OpenDeliveries::class)
        ->assertOk();
});
