<?php

use FluxErp\Models\BankConnection;
use FluxErp\Models\Contact;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Loan;
use FluxErp\Models\LoanInstallment;
use FluxErp\Models\Pivots\LoanInstallmentTransaction;
use FluxErp\Models\Transaction;

beforeEach(function (): void {
    $this->loan = Loan::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => Contact::factory()->create()->getKey(),
        'ledger_account_id' => LedgerAccount::factory()
            ->create(['tenant_id' => $this->dbTenant->getKey()])
            ->getKey(),
        'amount' => 12000,
        'number_of_installments' => 2,
    ]);
    $this->first = $this->loan->installments()->create([
        'sequence' => 1,
        'due_date' => now()->subDays(2)->toDateString(),
        'principal_amount' => 6000,
        'interest_amount' => 60,
    ]);
    $this->second = $this->loan->installments()->create([
        'sequence' => 2,
        'due_date' => now()->addMonth()->toDateString(),
        'principal_amount' => 6000,
        'interest_amount' => 30,
    ]);
    $this->bankConnection = BankConnection::factory()->create();
});

function tx(float $amount): Transaction
{
    return Transaction::factory()->create([
        'bank_connection_id' => test()->bankConnection->getKey(),
        'amount' => $amount,
        'balance' => $amount,
        'is_ignored' => false,
    ]);
}

test('a chargeback alone never settles an installment', function (): void {
    LoanInstallmentTransaction::create([
        'loan_installment_id' => $this->first->getKey(),
        'transaction_id' => tx(6060)->getKey(),
        'amount' => 6060,
        'is_accepted' => true,
    ]);

    expect(
        LoanInstallment::query()->settled()->whereKey($this->first->getKey())->exists()
    )->toBeFalse()
        ->and($this->loan->refresh()->remaining)->toEqual(12000);
});

test('un-accepting an assignment restores the transaction balance', function (): void {
    $transaction = tx(-6060);
    $pivot = LoanInstallmentTransaction::create([
        'loan_installment_id' => $this->first->getKey(),
        'transaction_id' => $transaction->getKey(),
        'amount' => -6060,
        'is_accepted' => true,
    ]);

    expect($transaction->refresh()->balance)->toEqual(0);

    $pivot->update(['is_accepted' => false]);

    expect($transaction->refresh()->balance)->toEqual(-6060)
        ->and($this->loan->refresh()->remaining)->toEqual(12000);
});

test('moving an assignment to another loan recalculates both loans', function (): void {
    $other = Loan::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => Contact::factory()->create()->getKey(),
        'ledger_account_id' => LedgerAccount::factory()
            ->create(['tenant_id' => $this->dbTenant->getKey()])
            ->getKey(),
        'amount' => 5000,
        'number_of_installments' => 1,
    ]);
    $otherInstallment = $other->installments()->create([
        'sequence' => 1,
        'due_date' => now()->toDateString(),
        'principal_amount' => 5000,
        'interest_amount' => 0,
    ]);

    $pivot = LoanInstallmentTransaction::create([
        'loan_installment_id' => $this->first->getKey(),
        'transaction_id' => tx(-6060)->getKey(),
        'amount' => -6060,
        'is_accepted' => true,
    ]);

    expect($this->loan->refresh()->remaining)->toEqual(6000);

    $pivot->update(['loan_installment_id' => $otherInstallment->getKey()]);

    expect($this->loan->refresh()->remaining)->toEqual(12000);
});

test('an overpaying transaction only covers what the installment needs', function (): void {
    $transaction = tx(-12090);

    LoanInstallmentTransaction::create([
        'loan_installment_id' => $this->first->getKey(),
        'transaction_id' => $transaction->getKey(),
        'amount' => -12090,
        'is_accepted' => true,
    ]);

    expect($this->loan->refresh()->remaining)->toEqual(6000);
});
