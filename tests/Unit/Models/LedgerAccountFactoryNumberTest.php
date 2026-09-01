<?php

use FluxErp\Enums\LedgerAccountTypeEnum;
use FluxErp\Models\LedgerAccount;

test('the factory numbers above what the table already holds', function (): void {
    // fake()->randomNumber() tops out at nine digits, so a ten digit row is one
    // the old factory could never clear. It only passes by reading the table.
    LedgerAccount::factory()->create([
        'number' => 1000000000,
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    $account = LedgerAccount::factory()->create(['tenant_id' => $this->dbTenant->getKey()]);

    expect($account->number)->toBeGreaterThan(1000000000);
});

test('the factory does not hand out a number twice', function (): void {
    $numbers = LedgerAccount::factory()
        ->count(50)
        ->create([
            'ledger_account_type_enum' => LedgerAccountTypeEnum::Liability,
            'tenant_id' => $this->dbTenant->getKey(),
        ])
        ->pluck('number');

    expect($numbers->unique())->toHaveCount(50);
});
