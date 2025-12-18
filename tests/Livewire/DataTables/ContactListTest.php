<?php

use FluxErp\Livewire\DataTables\ContactList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ContactList::class)
        ->assertOk();
});
