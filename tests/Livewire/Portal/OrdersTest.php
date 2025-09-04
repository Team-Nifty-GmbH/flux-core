<?php

use FluxErp\Livewire\Portal\Orders;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Orders::class)
        ->assertOk();
});
