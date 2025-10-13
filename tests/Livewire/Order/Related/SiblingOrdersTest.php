<?php

use FluxErp\Livewire\Order\Related\SiblingOrders;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(SiblingOrders::class)
        ->assertOk();
});
