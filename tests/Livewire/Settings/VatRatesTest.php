<?php

use FluxErp\Livewire\Settings\VatRates;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(VatRates::class)
        ->assertOk();
});
