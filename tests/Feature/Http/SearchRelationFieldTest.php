<?php

use FluxErp\Models\Contact;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Loan;
use FluxErp\Models\LoanInstallment;

test('a dotted search field searches the relation instead of failing', function (): void {
    $contact = Contact::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create();

    foreach (['Maschinenfinanzierung', 'Fuhrpark Leasing'] as $index => $name) {
        Loan::factory()
            ->create([
                'tenant_id' => $this->dbTenant->getKey(),
                'contact_id' => $contact->getKey(),
                'ledger_account_id' => LedgerAccount::factory()
                    ->create(['tenant_id' => $this->dbTenant->getKey()])
                    ->getKey(),
                'name' => $name,
            ])
            ->installments()
            ->create([
                'sequence' => $index + 1,
                'due_date' => now()->toDateString(),
                'principal_amount' => 100,
                'interest_amount' => 1,
            ]);
    }

    $response = $this->post(route('search', LoanInstallment::class), [
        'search' => 'Fuhrpark',
        'searchFields' => ['sequence', 'loan.name'],
    ]);

    $response->assertOk();

    $results = $response->json();

    expect($results)->toHaveCount(1)
        ->and(data_get($results, '0.label'))->toContain('Fuhrpark Leasing');
});
