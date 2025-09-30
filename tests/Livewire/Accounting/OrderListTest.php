<?php

use FluxErp\Livewire\Accounting\OrderList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(OrderList::class)
        ->assertOk();
});
