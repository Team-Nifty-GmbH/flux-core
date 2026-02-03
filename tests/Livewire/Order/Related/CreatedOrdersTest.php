<?php

use FluxErp\Livewire\Order\Related\CreatedOrders;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(CreatedOrders::class)
        ->assertOk();
});
