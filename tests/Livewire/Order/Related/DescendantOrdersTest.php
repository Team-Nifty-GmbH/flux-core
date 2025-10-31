<?php

use FluxErp\Livewire\Order\Related\DescendantOrders;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(DescendantOrders::class)
        ->assertOk();
});
