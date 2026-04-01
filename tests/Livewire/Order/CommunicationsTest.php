<?php

use FluxErp\Livewire\Order\Communications;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Communications::class)
        ->assertOk();
});
