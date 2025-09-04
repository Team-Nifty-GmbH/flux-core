<?php

use FluxErp\Livewire\DataTables\Transactions\OrderList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(OrderList::class)
        ->assertOk();
});
