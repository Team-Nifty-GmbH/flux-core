<?php

use FluxErp\Livewire\Order\Related\FamilyOrders;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(FamilyOrders::class)
        ->assertOk();
});
