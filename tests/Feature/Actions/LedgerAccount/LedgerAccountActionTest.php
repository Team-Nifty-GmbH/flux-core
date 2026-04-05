<?php

use FluxErp\Actions\LedgerAccount\CreateLedgerAccount;
use FluxErp\Actions\LedgerAccount\DeleteLedgerAccount;
use FluxErp\Actions\LedgerAccount\UpdateLedgerAccount;
use FluxErp\Enums\LedgerAccountTypeEnum;
use FluxErp\Models\LedgerAccount;

test('create ledger account', function (): void {
    $account = CreateLedgerAccount::make([
        'name' => 'Revenue',
        'number' => '8000',
        'ledger_account_type_enum' => LedgerAccountTypeEnum::Revenue->value,
        'tenant_id' => $this->dbTenant->getKey(),
    ])->validate()->execute();

    expect($account)->toBeInstanceOf(LedgerAccount::class)
        ->name->toBe('Revenue')
        ->number->toBe('8000');
});

test('create ledger account auto-sets default tenant', function (): void {
    $account = CreateLedgerAccount::make([
        'name' => 'Costs',
        'number' => '4000',
        'ledger_account_type_enum' => LedgerAccountTypeEnum::Expense->value,
    ])->validate()->execute();

    expect($account->tenant_id)->toBe($this->dbTenant->getKey());
});

test('create ledger account requires name number and type', function (): void {
    CreateLedgerAccount::assertValidationErrors([], ['name', 'number', 'ledger_account_type_enum']);
});

test('create ledger account rejects duplicate number per tenant and type', function (): void {
    CreateLedgerAccount::make([
        'name' => 'First',
        'number' => '8000',
        'ledger_account_type_enum' => LedgerAccountTypeEnum::Revenue->value,
        'tenant_id' => $this->dbTenant->getKey(),
    ])->validate()->execute();

    CreateLedgerAccount::assertValidationErrors([
        'name' => 'Duplicate',
        'number' => '8000',
        'ledger_account_type_enum' => LedgerAccountTypeEnum::Revenue->value,
        'tenant_id' => $this->dbTenant->getKey(),
    ], 'number');
});

test('update ledger account', function (): void {
    $account = LedgerAccount::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    $updated = UpdateLedgerAccount::make([
        'id' => $account->getKey(),
        'name' => 'Updated Account',
    ])->validate()->execute();

    expect($updated->name)->toBe('Updated Account');
});

test('delete ledger account', function (): void {
    $account = LedgerAccount::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    expect(DeleteLedgerAccount::make(['id' => $account->getKey()])
        ->validate()->execute())->toBeTrue();
});
