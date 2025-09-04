<?php

use FluxErp\Livewire\DataTables\ContactBankConnectionList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ContactBankConnectionList::class)
        ->assertOk();
});
