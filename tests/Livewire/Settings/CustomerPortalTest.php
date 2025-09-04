<?php

use FluxErp\Livewire\Settings\CustomerPortal;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(CustomerPortal::class)
        ->assertOk();
});
