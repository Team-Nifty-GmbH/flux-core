<?php

use FluxErp\Livewire\Settings\Printers;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Printers::class)
        ->assertOk();
});
