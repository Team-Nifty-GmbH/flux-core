<?php

use FluxErp\Enums\RepaymentTypeEnum;
use FluxErp\Livewire\Accounting\Loans;
use FluxErp\Models\Contact;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Loan;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->contact = Contact::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create();
    $this->ledgerAccount = LedgerAccount::factory()->create(['tenant_id' => $this->dbTenant->getKey()]);
});

test('renders successfully', function (): void {
    Livewire::test(Loans::class)
        ->assertOk();
});

test('edit with null resets form and opens modal', function (): void {
    Livewire::test(Loans::class)
        ->call('edit')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('loan.id', null)
        ->assertSet('installments', [])
        ->assertOpensModal('edit-loan-modal');
});

test('can create a loan with its schedule', function (): void {
    Livewire::test(Loans::class)
        ->call('edit')
        ->set('loan.contact_id', $this->contact->getKey())
        ->set('loan.ledger_account_id', $this->ledgerAccount->getKey())
        ->set('loan.name', 'Machine financing')
        ->set('loan.amount', 12000)
        ->set('loan.interest_rate', 0.06)
        ->set('loan.repayment_type_enum', RepaymentTypeEnum::Annuity->value)
        ->set('loan.number_of_installments', 12)
        ->set('loan.starts_at', '2026-01-01')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors();

    $this->assertDatabaseHas('loans', ['name' => 'Machine financing']);
    $this->assertDatabaseCount('loan_installments', 12);
});

test('edit populates the schedule for an existing loan', function (): void {
    $loan = Loan::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $this->contact->getKey(),
        'ledger_account_id' => $this->ledgerAccount->getKey(),
        'amount' => 12000,
        'number_of_installments' => 2,
    ]);
    $loan->installments()->create([
        'sequence' => 1,
        'due_date' => '2026-02-01',
        'principal_amount' => 6000,
        'interest_amount' => 0,
    ]);
    $loan->installments()->create([
        'sequence' => 2,
        'due_date' => '2026-03-01',
        'principal_amount' => 6000,
        'interest_amount' => 0,
    ]);

    Livewire::test(Loans::class)
        ->call('edit', $loan->getKey())
        ->assertOk()
        ->assertSet('loan.id', $loan->getKey())
        ->assertCount('installments', 2);
});

test('can delete a loan', function (): void {
    $loan = Loan::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $this->contact->getKey(),
        'ledger_account_id' => $this->ledgerAccount->getKey(),
    ]);

    Livewire::test(Loans::class)
        ->call('delete', $loan->getKey())
        ->assertOk()
        ->assertHasNoErrors();

    $this->assertSoftDeleted('loans', ['id' => $loan->getKey()]);
});
