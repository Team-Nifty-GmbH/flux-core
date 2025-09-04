<?php

use FluxErp\Livewire\DataTables\AddressTypeList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(AddressTypeList::class)
        ->assertOk();
});
