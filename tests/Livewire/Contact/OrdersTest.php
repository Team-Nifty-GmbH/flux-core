<?php

use FluxErp\Livewire\Contact\Orders;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Orders::class)
        ->assertOk();
});
