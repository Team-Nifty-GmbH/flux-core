<?php

use FluxErp\Actions\Tenant\CreateTenant;
use FluxErp\Actions\Tenant\DeleteTenant;
use FluxErp\Actions\Tenant\UpdateTenant;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Tenant;

test('create tenant', function (): void {
    $tenant = CreateTenant::make([
        'name' => 'Acme Inc.',
        'tenant_code' => 'ACME',
    ])->validate()->execute();

    expect($tenant)->toBeInstanceOf(Tenant::class)
        ->name->toBe('Acme Inc.');
});

test('update tenant', function (): void {
    $tenant = Tenant::factory()->create();

    $updated = UpdateTenant::make([
        'id' => $tenant->getKey(),
        'name' => 'Updated Tenant',
    ])->validate()->execute();

    expect($updated->name)->toBe('Updated Tenant');
});

test('delete tenant', function (): void {
    $tenant = Tenant::factory()->create();

    expect(DeleteTenant::make(['id' => $tenant->getKey()])
        ->validate()->execute())->toBeTrue();
});

test('update tenant sets a clearing ledger account of its own', function (): void {
    $ledgerAccount = LedgerAccount::factory()->create(['tenant_id' => $this->dbTenant->getKey()]);

    UpdateTenant::make([
        'id' => $this->dbTenant->getKey(),
        'clearing_ledger_account_id' => $ledgerAccount->getKey(),
    ])->validate()->execute();

    expect($this->dbTenant->refresh()->clearing_ledger_account_id)->toBe($ledgerAccount->getKey());
});

test('update tenant rejects a clearing ledger account of another tenant', function (): void {
    $ledgerAccount = LedgerAccount::factory()->create(['tenant_id' => Tenant::factory()->create()->getKey()]);

    UpdateTenant::assertValidationErrors([
        'id' => $this->dbTenant->getKey(),
        'clearing_ledger_account_id' => $ledgerAccount->getKey(),
    ], 'clearing_ledger_account_id');

    expect($this->dbTenant->refresh()->clearing_ledger_account_id)->toBeNull();
});
