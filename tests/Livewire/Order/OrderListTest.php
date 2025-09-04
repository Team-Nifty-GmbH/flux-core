<?php

use FluxErp\Livewire\Order\OrderList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(OrderList::class)
        ->assertStatus(200);
});
