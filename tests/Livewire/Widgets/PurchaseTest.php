<?php

use FluxErp\Livewire\Widgets\Purchase;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Purchase::class)
        ->assertOk();
});
