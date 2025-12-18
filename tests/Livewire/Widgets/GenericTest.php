<?php

use FluxErp\Livewire\Widgets\Generic;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Generic::class)
        ->assertOk();
});
