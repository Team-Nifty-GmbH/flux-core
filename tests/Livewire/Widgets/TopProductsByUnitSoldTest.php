<?php

use FluxErp\Livewire\Widgets\TopProductsByUnitSold;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(TopProductsByUnitSold::class)
        ->assertOk();
});
