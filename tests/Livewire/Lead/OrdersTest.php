<?php

use FluxErp\Livewire\Lead\Orders;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Orders::class)
        ->assertOk();
});
