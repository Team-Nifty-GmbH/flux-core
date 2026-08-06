<?php

use FluxErp\Models\Contact;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Loan;
use FluxErp\Models\LoanInstallment;
use FluxErp\Models\Tenant;
use Illuminate\Support\Facades\Context;

test('an installment of a foreign tenant is not visible', function (): void {
    $foreignTenant = Tenant::factory()->create();

    $loan = Loan::factory()->create([
        'tenant_id' => $foreignTenant->getKey(),
        'contact_id' => Contact::factory()->create()->getKey(),
        'ledger_account_id' => LedgerAccount::factory()
            ->create(['tenant_id' => $foreignTenant->getKey()])
            ->getKey(),
        'amount' => 1000,
        'number_of_installments' => 1,
    ]);
    $installment = $loan->installments()->create([
        'sequence' => 1,
        'due_date' => now()->toDateString(),
        'principal_amount' => 1000,
        'interest_amount' => 0,
    ]);

    $this->user->tenants()->syncWithoutDetaching([$this->dbTenant->getKey()]);

    Context::forget('user_tenant_ids');
    $this->actingAs($this->user, 'web');

    expect(LoanInstallment::query()->whereKey($installment->getKey())->exists())->toBeFalse();
});
