<?php

use FluxErp\Livewire\Product\SerialNumber\SerialNumberList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(SerialNumberList::class)
        ->assertOk();
});
