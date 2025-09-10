<?php

use FluxErp\Livewire\DataTables\OrderTransactionList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(OrderTransactionList::class)
        ->assertOk();
});
