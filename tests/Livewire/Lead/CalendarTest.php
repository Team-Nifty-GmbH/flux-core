<?php

use FluxErp\Livewire\Lead\Calendar;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Calendar::class)
        ->assertOk();
});
