<?php

use FluxErp\Livewire\Settings\OrderTypes;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(OrderTypes::class)
        ->assertOk();
});
