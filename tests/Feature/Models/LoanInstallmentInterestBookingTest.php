<?php

use FluxErp\Models\BankConnection;
use FluxErp\Models\Contact;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Loan;
use FluxErp\Models\Pivots\LoanInstallmentTransaction;
use FluxErp\Models\Transaction;

beforeEach(function (): void {
    $this->loanLedgerAccount = LedgerAccount::factory()->create(['tenant_id' => $this->dbTenant->getKey()]);
    $this->interestLedgerAccount = LedgerAccount::factory()->create(['tenant_id' => $this->dbTenant->getKey()]);
    $this->bankLedgerAccount = LedgerAccount::factory()->create(['tenant_id' => $this->dbTenant->getKey()]);

    $this->loan = Loan::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => Contact::factory()->create()->getKey(),
        'ledger_account_id' => $this->loanLedgerAccount->getKey(),
        'interest_ledger_account_id' => $this->interestLedgerAccount->getKey(),
        'amount' => 12000,
        'number_of_installments' => 2,
    ]);
    $this->installment = $this->loan->installments()->create([
        'sequence' => 1,
        'due_date' => now()->subDays(2)->toDateString(),
        'principal_amount' => 6000,
        'interest_amount' => 60,
    ]);
    $this->bankConnection = BankConnection::factory()->create([
        'ledger_account_id' => $this->bankLedgerAccount->getKey(),
    ]);
});

function interestRepayment(float $amount): Transaction
{
    return Transaction::factory()->create([
        'bank_connection_id' => test()->bankConnection->getKey(),
        'amount' => $amount,
        'balance' => $amount,
        'booking_date' => '2026-03-15',
        'is_ignored' => false,
    ]);
}

test('a full repayment books interest and principal separately', function (): void {
    $assignment = LoanInstallmentTransaction::create([
        'loan_installment_id' => $this->installment->getKey(),
        'transaction_id' => interestRepayment(-6060)->getKey(),
        'amount' => -6060,
        'is_accepted' => true,
    ]);

    $bookings = $assignment->ledgerBookings()->get()->keyBy('debit_ledger_account_id');

    expect($bookings)->toHaveCount(2)
        ->and($bookings[$this->interestLedgerAccount->getKey()]->amount)->toEqual(60)
        ->and($bookings[$this->interestLedgerAccount->getKey()]->credit_ledger_account_id)
        ->toEqual($this->bankLedgerAccount->getKey())
        ->and($bookings[$this->loanLedgerAccount->getKey()]->amount)->toEqual(6000)
        ->and($bookings[$this->loanLedgerAccount->getKey()]->credit_ledger_account_id)
        ->toEqual($this->bankLedgerAccount->getKey());
});

test('a payment below the interest share books interest only', function (): void {
    $assignment = LoanInstallmentTransaction::create([
        'loan_installment_id' => $this->installment->getKey(),
        'transaction_id' => interestRepayment(-40)->getKey(),
        'amount' => -40,
        'is_accepted' => true,
    ]);

    $bookings = $assignment->ledgerBookings()->get();

    expect($bookings)->toHaveCount(1)
        ->and($bookings->first()->debit_ledger_account_id)->toEqual($this->interestLedgerAccount->getKey())
        ->and($bookings->first()->amount)->toEqual(40);
});

test('a second payment continues with the principal once the interest is covered', function (): void {
    LoanInstallmentTransaction::create([
        'loan_installment_id' => $this->installment->getKey(),
        'transaction_id' => interestRepayment(-60)->getKey(),
        'amount' => -60,
        'is_accepted' => true,
    ]);

    $second = LoanInstallmentTransaction::create([
        'loan_installment_id' => $this->installment->getKey(),
        'transaction_id' => interestRepayment(-6000)->getKey(),
        'amount' => -6000,
        'is_accepted' => true,
    ]);

    $bookings = $second->ledgerBookings()->get();

    expect($bookings)->toHaveCount(1)
        ->and($bookings->first()->debit_ledger_account_id)->toEqual($this->loanLedgerAccount->getKey())
        ->and($bookings->first()->amount)->toEqual(6000);
});

test('an interest free installment books everything against the loan account', function (): void {
    $installment = $this->loan->installments()->create([
        'sequence' => 2,
        'due_date' => now()->addMonth()->toDateString(),
        'principal_amount' => 6000,
        'interest_amount' => 0,
    ]);

    $assignment = LoanInstallmentTransaction::create([
        'loan_installment_id' => $installment->getKey(),
        'transaction_id' => interestRepayment(-6000)->getKey(),
        'amount' => -6000,
        'is_accepted' => true,
    ]);

    $bookings = $assignment->ledgerBookings()->get();

    expect($bookings)->toHaveCount(1)
        ->and($bookings->first()->debit_ledger_account_id)->toEqual($this->loanLedgerAccount->getKey())
        ->and($bookings->first()->amount)->toEqual(6000);
});

test('a loan without an interest account keeps the single booking', function (): void {
    $this->loan->interest_ledger_account_id = null;
    $this->loan->save();

    $assignment = LoanInstallmentTransaction::create([
        'loan_installment_id' => $this->installment->getKey(),
        'transaction_id' => interestRepayment(-6060)->getKey(),
        'amount' => -6060,
        'is_accepted' => true,
    ]);

    $bookings = $assignment->ledgerBookings()->get();

    expect($bookings)->toHaveCount(1)
        ->and($bookings->first()->debit_ledger_account_id)->toEqual($this->loanLedgerAccount->getKey())
        ->and($bookings->first()->amount)->toEqual(6060);
});

test('a chargeback reverses interest and principal', function (): void {
    $assignment = LoanInstallmentTransaction::create([
        'loan_installment_id' => $this->installment->getKey(),
        'transaction_id' => interestRepayment(6060)->getKey(),
        'amount' => 6060,
        'is_accepted' => true,
    ]);

    $bookings = $assignment->ledgerBookings()->get()->keyBy('credit_ledger_account_id');

    expect($bookings)->toHaveCount(2)
        ->and($bookings[$this->interestLedgerAccount->getKey()]->debit_ledger_account_id)
        ->toEqual($this->bankLedgerAccount->getKey())
        ->and($bookings[$this->interestLedgerAccount->getKey()]->amount)->toEqual(60)
        ->and($bookings[$this->loanLedgerAccount->getKey()]->amount)->toEqual(6000);
});

test('lowering the amount drops the principal booking', function (): void {
    $assignment = LoanInstallmentTransaction::create([
        'loan_installment_id' => $this->installment->getKey(),
        'transaction_id' => interestRepayment(-6060)->getKey(),
        'amount' => -6060,
        'is_accepted' => true,
    ]);

    expect($assignment->ledgerBookings()->count())->toEqual(2);

    $assignment->update(['amount' => -50]);

    $bookings = $assignment->ledgerBookings()->get();

    expect($bookings)->toHaveCount(1)
        ->and($bookings->first()->debit_ledger_account_id)->toEqual($this->interestLedgerAccount->getKey())
        ->and($bookings->first()->amount)->toEqual(50);
});
