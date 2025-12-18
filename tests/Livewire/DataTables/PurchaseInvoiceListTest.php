<?php

use FluxErp\Livewire\DataTables\PurchaseInvoiceList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(PurchaseInvoiceList::class)
        ->assertOk();
});
