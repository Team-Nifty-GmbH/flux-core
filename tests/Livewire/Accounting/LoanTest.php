<?php

use FluxErp\Livewire\Accounting\Loan;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Loan as LoanModel;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

beforeEach(function (): void {
    // the amounts are asserted with two decimals, so the currency must not be a
    // random one from the factory
    Currency::query()->update(['is_default' => false]);
    ($euro = Currency::query()->firstWhere('iso', 'EUR'))
        ? $euro->update(['is_default' => true])
        : Currency::factory()->create(['iso' => 'EUR', 'name' => 'Euro', 'is_default' => true]);

    $this->contact = Contact::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create();
    $this->ledgerAccount = LedgerAccount::factory()->create(['tenant_id' => $this->dbTenant->getKey()]);
    $this->loan = LoanModel::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $this->contact->getKey(),
        'ledger_account_id' => $this->ledgerAccount->getKey(),
        'name' => 'Machine financing',
        'amount' => 12000,
        'interest_rate' => 0.02299,
        'number_of_installments' => 2,
    ]);
    $this->loan->installments()->create([
        'sequence' => 1,
        'due_date' => '2026-02-01',
        'principal_amount' => 6000,
        'interest_amount' => 60,
    ]);
    $this->loan->installments()->create([
        'sequence' => 2,
        'due_date' => '2026-03-01',
        'principal_amount' => 6000,
        'interest_amount' => 30,
    ]);
});

test('renders successfully', function (): void {
    Livewire::test(Loan::class, ['id' => $this->loan->getKey()])
        ->assertOk()
        ->assertSet('loan.id', $this->loan->getKey())
        ->assertCount('installments', 2)
        // stored as a factor, shown as a percentage
        ->assertSet('loan.interest_rate', 2.299);
});

test('every tab renders', function (string $tab): void {
    Livewire::test(Loan::class, ['id' => $this->loan->getKey()])
        ->set('tab', $tab)
        ->assertOk()
        ->assertNoRedirect();
})->with([
    'loan.general',
    'loan.installments',
    'loan.payments',
    'loan.extra-repayments',
    'loan.documents',
]);

test('the extra repayment tab shows the allowance and books a repayment', function (): void {
    $this->loan->update([
        'installment_amount' => 6060,
        'extra_repayment_allowance_amount' => 5000,
    ]);

    Livewire::test(Loan::class, ['id' => $this->loan->getKey()])
        ->set('tab', 'loan.extra-repayments')
        ->assertOk()
        ->assertSet('allowance.is_allowed', true)
        ->assertSet('allowance.is_capped', true)
        ->set('extraRepayment.amount', 3000)
        ->set('extraRepayment.executed_at', '2026-01-15')
        ->assertSet('extraRepayments', [])
        ->call('saveExtraRepayment')
        ->assertHasNoErrors()
        ->assertCount('extraRepayments', 1);

    expect($this->loan->refresh()->remaining)->toEqual(9000);
});

test('the extra repayment tab refuses a loan without an allowance', function (): void {
    $this->loan->update(['allows_extra_repayments' => false]);

    Livewire::test(Loan::class, ['id' => $this->loan->getKey()])
        ->set('tab', 'loan.extra-repayments')
        ->assertOk()
        ->assertSet('allowance.is_allowed', false)
        ->set('extraRepayment.amount', 100)
        ->call('saveExtraRepayment')
        ->assertReturned(false);

    expect($this->loan->refresh()->extraRepayments()->count())->toBe(0);
});

test('the schedule tab formats amounts and dates for the locale', function (): void {
    app()->setLocale('de');

    Livewire::test(Loan::class, ['id' => $this->loan->getKey()])
        ->set('tab', 'loan.installments')
        ->assertOk()
        ->assertSee('01.02.2026')
        ->assertSee('6.000,00')
        ->assertSet('totals.principal_amount', fn (string $value) => str_contains($value, '12.000,00'))
        ->assertSet('totals.interest_amount', fn (string $value) => str_contains($value, '90,00'))
        ->assertSet('totals.total', fn (string $value) => str_contains($value, '12.090,00'));
});

test('the totals show how much of the loan is settled', function (): void {
    app()->setLocale('de');

    $this->loan->installments()->orderBy('sequence')->first()->update(['is_paid' => true]);

    Livewire::test(Loan::class, ['id' => $this->loan->getKey()])
        ->assertOk()
        ->assertSet('totals.paid_principal_amount', fn (string $value) => str_contains($value, '6.000,00'))
        ->assertSet('totals.paid_interest_amount', fn (string $value) => str_contains($value, '60,00'))
        ->assertSet('totals.paid_total', fn (string $value) => str_contains($value, '6.060,00'))
        ->assertSet('totals.paid_principal_share', fn (string $value) => str_contains($value, '50'))
        ->assertSet('totals.paid_total_share', fn (string $value) => str_contains($value, '50,1'));
});

test('an unknown loan is not found', function (): void {
    Livewire::test(Loan::class, ['id' => $this->loan->getKey() + 1]);
})->throws(Illuminate\Database\Eloquent\ModelNotFoundException::class);

test('can save the loan', function (): void {
    Livewire::test(Loan::class, ['id' => $this->loan->getKey()])
        ->set('loan.name', 'Renamed loan')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors();

    $this->assertDatabaseHas('loans', [
        'id' => $this->loan->getKey(),
        'name' => 'Renamed loan',
    ]);
});

test('the payments tab lists the assigned transactions', function (): void {
    $transaction = FluxErp\Models\Transaction::factory()->create([
        'bank_connection_id' => FluxErp\Models\BankConnection::factory()->create()->getKey(),
        'amount' => -6060,
        'purpose' => 'Rate 1 DARL-1',
    ]);

    FluxErp\Models\Pivots\LoanInstallmentTransaction::create([
        'loan_installment_id' => $this->loan->installments()->orderBy('sequence')->value('id'),
        'transaction_id' => $transaction->getKey(),
        'amount' => -6060,
        'is_accepted' => true,
    ]);

    Livewire::test(Loan::class, ['id' => $this->loan->getKey()])
        ->set('tab', 'loan.payments')
        ->assertOk()
        ->assertCount('payments', 1)
        ->assertSet('payments.0.purpose', 'Rate 1 DARL-1')
        ->assertSet('payments.0.is_accepted', true)
        ->assertSee('Rate 1 DARL-1');
});

test('the totals count an installment settled by a transaction', function (): void {
    $transaction = FluxErp\Models\Transaction::factory()->create([
        'bank_connection_id' => FluxErp\Models\BankConnection::factory()->create()->getKey(),
        'amount' => -6060,
    ]);

    FluxErp\Models\Pivots\LoanInstallmentTransaction::create([
        'loan_installment_id' => $this->loan->installments()->orderBy('sequence')->value('id'),
        'transaction_id' => $transaction->getKey(),
        'amount' => -6060,
        'is_accepted' => true,
    ]);

    app()->setLocale('de');

    Livewire::test(Loan::class, ['id' => $this->loan->getKey()])
        ->assertOk()
        ->assertSet('totals.paid_principal_amount', fn (string $value) => str_contains($value, '6.000,00'))
        ->assertSet('totals.paid_interest_amount', fn (string $value) => str_contains($value, '60,00'));
});

test('a settled installment shows its status in the schedule', function (): void {
    $transaction = FluxErp\Models\Transaction::factory()->create([
        'bank_connection_id' => FluxErp\Models\BankConnection::factory()->create()->getKey(),
        'amount' => -6060,
    ]);

    FluxErp\Models\Pivots\LoanInstallmentTransaction::create([
        'loan_installment_id' => $this->loan->installments()->orderBy('sequence')->value('id'),
        'transaction_id' => $transaction->getKey(),
        'amount' => -6060,
        'is_accepted' => true,
    ]);

    Livewire::test(Loan::class, ['id' => $this->loan->getKey()])
        ->assertOk()
        ->assertSet('installments.0.status', __('Settled'))
        ->assertSet('installments.1.status', __('Overdue'));
});

test('can attach a contract', function (): void {
    Livewire::test(Loan::class, ['id' => $this->loan->getKey()])
        ->set('contract.file', [UploadedFile::fake()->image('contract.jpg')])
        ->call('saveContract')
        ->assertOk()
        ->assertHasNoErrors();

    expect($this->loan->refresh()->getMedia('contract'))->toHaveCount(1);
});

test('saving the loan leaves the contract untouched', function (): void {
    Livewire::test(Loan::class, ['id' => $this->loan->getKey()])
        ->set('loan.name', 'Renamed loan')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors();

    expect($this->loan->refresh()->getMedia('contract'))->toHaveCount(0);
});

test('moving an installment in time does not touch the loan', function (): void {
    $installment = $this->loan->installments()->orderBy('sequence')->first();

    // a recalculation would write the loan and move its timestamp
    DB::table('loans')->where('id', $this->loan->getKey())->update(['updated_at' => '2020-01-01 00:00:00']);

    $installment->update(['due_date' => '2026-04-01', 'sequence' => 7]);

    expect(DB::table('loans')->where('id', $this->loan->getKey())->value('updated_at'))
        ->toBe('2020-01-01 00:00:00');

    $installment->update(['is_paid' => true]);

    expect(DB::table('loans')->where('id', $this->loan->getKey())->value('updated_at'))
        ->not->toBe('2020-01-01 00:00:00');
});

test('resetting the form restores the stored values', function (): void {
    Livewire::test(Loan::class, ['id' => $this->loan->getKey()])
        ->set('loan.name', 'Discarded')
        ->call('resetForm')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('loan.name', 'Machine financing');
});

test('can delete the loan', function (): void {
    Livewire::test(Loan::class, ['id' => $this->loan->getKey()])
        ->call('delete')
        ->assertOk()
        ->assertRedirect(route('accounting.loans'));

    $this->assertSoftDeleted('loans', ['id' => $this->loan->getKey()]);
});
