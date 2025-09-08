<?php

use FluxErp\Livewire\Portal\DataTables\OrderList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(OrderList::class)
        ->assertOk();
});
