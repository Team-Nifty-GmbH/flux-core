<?php

use FluxErp\Actions\PurchaseInvoice\DeletePurchaseInvoice;
use FluxErp\Actions\PurchaseInvoice\UpdatePurchaseInvoice;
use FluxErp\Models\PurchaseInvoice;

test('update purchase invoice', function (): void {
    $pi = PurchaseInvoice::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    $updated = UpdatePurchaseInvoice::make([
        'id' => $pi->getKey(),
        'invoice_number' => 'PI-2026-001',
    ])->validate()->execute();

    expect($updated->invoice_number)->toBe('PI-2026-001');
});

test('delete purchase invoice', function (): void {
    $pi = PurchaseInvoice::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    $result = DeletePurchaseInvoice::make(['id' => $pi->getKey()])
        ->validate()->execute();

    expect($result)->toBeTrue();
});
