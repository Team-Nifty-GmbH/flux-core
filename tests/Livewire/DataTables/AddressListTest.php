<?php

use FluxErp\Livewire\DataTables\AddressList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(AddressList::class)
        ->assertOk();
});
