<?php

use FluxErp\Livewire\Settings\Warehouses;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Warehouses::class)
        ->assertOk();
});
