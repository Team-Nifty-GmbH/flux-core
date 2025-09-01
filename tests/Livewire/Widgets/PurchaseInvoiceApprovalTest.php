<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Widgets\PurchaseInvoiceApproval;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(PurchaseInvoiceApproval::class)
        ->assertStatus(200);
});
