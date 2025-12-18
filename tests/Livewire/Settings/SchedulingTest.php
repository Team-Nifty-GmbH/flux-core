<?php

use FluxErp\Livewire\Settings\Scheduling;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Scheduling::class)
        ->assertOk();
});
