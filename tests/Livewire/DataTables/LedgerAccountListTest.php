<?php

use FluxErp\Livewire\DataTables\LedgerAccountList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(LedgerAccountList::class)
        ->assertOk();
});
