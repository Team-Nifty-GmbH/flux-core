<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\DataTables\PurchaseInvoiceList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(PurchaseInvoiceList::class)
        ->assertStatus(200);
});
