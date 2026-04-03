<?php

use FluxErp\Livewire\Settings\CountryRegions;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(CountryRegions::class)
        ->assertOk();
});
