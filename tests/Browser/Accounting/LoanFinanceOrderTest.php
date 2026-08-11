<?php

use FluxErp\Models\Contact;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Loan;

beforeEach(function (): void {
    $contact = Contact::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create();

    $this->loan = Loan::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $contact->getKey(),
        'ledger_account_id' => LedgerAccount::factory()
            ->create(['tenant_id' => $this->dbTenant->getKey()])
            ->getKey(),
        'name' => 'Machine financing',
        'amount' => 12000,
        'interest_rate' => 0,
        'number_of_installments' => 2,
        'order_id' => null,
    ]);
});

test('the loan detail opens the finance order modal', function (): void {
    $page = visit(route('accounting.loans.id', ['id' => $this->loan->getKey()]))
        ->assertNoSmoke();

    waitForCondition($page, <<<'JS'
        () => document.querySelector('[x-on\\:click*="finance-order"]') !== null
    JS);

    $page->script(<<<'JS'
        () => document.querySelector('[x-on\\:click*="$tsui.open.modal(\'finance-order\')"]').click()
    JS);

    waitForCondition($page, <<<'JS'
        () => document.querySelector('#finance-order')?.offsetParent !== null
    JS);

    $page->assertNoJavascriptErrors();
});
