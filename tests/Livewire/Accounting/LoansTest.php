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
        ->assertOpensModal('edit-loan-modal');
});

test('can create a loan with its schedule', function (): void {
    Livewire::test(Loans::class)
        ->call('edit')
        ->set('loan.contact_id', $this->contact->getKey())
        ->set('loan.ledger_account_id', $this->ledgerAccount->getKey())
        ->set('loan.name', 'Machine financing')
        ->set('loan.amount', 12000)
        ->set('loan.interest_rate', 6)
        ->set('loan.repayment_type_enum', RepaymentTypeEnum::Annuity->value)
        ->set('loan.number_of_installments', 12)
        ->set('loan.starts_at', '2026-01-01')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertRedirect(
            route('accounting.loans.id', ['id' => Loan::query()->value('id')])
        );

    $this->assertDatabaseHas('loans', [
        'name' => 'Machine financing',
        'interest_rate' => 0.06,
    ]);
    $this->assertDatabaseCount('loan_installments', 12);
});

test('a tiny interest rate does not reach bcmath in scientific notation', function (): void {
    Livewire::test(Loans::class)
        ->call('edit')
        ->set('loan.contact_id', $this->contact->getKey())
        ->set('loan.ledger_account_id', $this->ledgerAccount->getKey())
        ->set('loan.name', 'Almost free money')
        ->set('loan.amount', 12000)
        ->set('loan.interest_rate', 0.00001)
        ->set('loan.repayment_type_enum', RepaymentTypeEnum::Annuity->value)
        ->set('loan.number_of_installments', 12)
        ->set('loan.starts_at', '2026-01-01')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors();

    $this->assertDatabaseHas('loans', [
        'name' => 'Almost free money',
        'interest_rate' => 0.0000001,
    ]);
});

test('the list sums the total interest over the whole term', function (): void {
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
        'interest_amount' => 60,
    ]);
    $loan->installments()->create([
        'sequence' => 2,
        'due_date' => '2026-03-01',
        'principal_amount' => 6000,
        'interest_amount' => 30,
        'is_paid' => true,
    ]);

    // the schedule was created without the action, so the loan has to catch up once
    $loan->calculateTotalInterest()->save();

    $data = Livewire::test(Loans::class)
        ->call('loadData')
        ->assertOk()
        ->instance()
        ->getDataForTesting();

    expect(data_get($data, 'data.0.total_interest.raw'))->toEqual(90)
        ->and(data_get($data, 'data.0.total_interest.display'))->toContain('text-green-600')
        ->and(data_get($data, 'data.0.remaining.raw'))->toEqual(6000);
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
