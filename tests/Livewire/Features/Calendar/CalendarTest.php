<?php

use FluxErp\Livewire\Features\Calendar\Calendar;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Calendar::class)
        ->assertOk();
});
