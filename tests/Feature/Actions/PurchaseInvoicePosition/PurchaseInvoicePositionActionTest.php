<?php

use FluxErp\Actions\PurchaseInvoicePosition\CreatePurchaseInvoicePosition;
use FluxErp\Actions\PurchaseInvoicePosition\DeletePurchaseInvoicePosition;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\PurchaseInvoice;
use FluxErp\Models\VatRate;

beforeEach(function (): void {
    $this->purchaseInvoice = PurchaseInvoice::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);
    $this->ledgerAccount = LedgerAccount::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);
    $this->vatRate = VatRate::factory()->create();
});

test('create purchase invoice position', function (): void {
    $position = CreatePurchaseInvoicePosition::make([
        'purchase_invoice_id' => $this->purchaseInvoice->getKey(),
        'ledger_account_id' => $this->ledgerAccount->getKey(),
        'vat_rate_id' => $this->vatRate->getKey(),
        'name' => 'Office Supplies',
        'amount' => 1,
        'unit_price' => 100.00,
        'total_price' => 100.00,
    ])->validate()->execute();

    expect($position)->name->toBe('Office Supplies');
});

test('create purchase invoice position requires purchase_invoice_id', function (): void {
    CreatePurchaseInvoicePosition::assertValidationErrors([], 'purchase_invoice_id');
});

test('delete purchase invoice position', function (): void {
    $position = CreatePurchaseInvoicePosition::make([
        'purchase_invoice_id' => $this->purchaseInvoice->getKey(),
        'ledger_account_id' => $this->ledgerAccount->getKey(),
        'vat_rate_id' => $this->vatRate->getKey(),
        'name' => 'Temp',
        'amount' => 1,
        'unit_price' => 50.00,
        'total_price' => 50.00,
    ])->validate()->execute();

    expect(DeletePurchaseInvoicePosition::make(['id' => $position->getKey()])
        ->validate()->execute())->toBeTrue();
});
