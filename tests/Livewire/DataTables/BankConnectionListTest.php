<?php

use FluxErp\Livewire\DataTables\BankConnectionList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(BankConnectionList::class)
        ->assertOk();
});
