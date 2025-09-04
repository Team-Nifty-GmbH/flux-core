<?php

use FluxErp\Livewire\DataTables\WarehouseList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(WarehouseList::class)
        ->assertOk();
});
