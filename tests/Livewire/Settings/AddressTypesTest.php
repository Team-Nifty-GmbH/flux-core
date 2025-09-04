<?php

use FluxErp\Livewire\Settings\AddressTypes;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(AddressTypes::class)
        ->assertOk();
});
