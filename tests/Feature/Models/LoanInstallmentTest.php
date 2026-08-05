<?php

use FluxErp\Models\BankConnection;
use FluxErp\Models\Contact;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Loan;
use FluxErp\Models\LoanInstallment;
use FluxErp\Models\Pivots\LoanInstallmentTransaction;
use FluxErp\Models\Transaction;

beforeEach(function (): void {
    $this->contact = Contact::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create();
    $this->loan = Loan::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $this->contact->getKey(),
        'ledger_account_id' => LedgerAccount::factory()
            ->create(['tenant_id' => $this->dbTenant->getKey()])
            ->getKey(),
        'amount' => 12000,
        'number_of_installments' => 2,
    ]);
    $this->installment = $this->loan->installments()->create([
        'sequence' => 1,
        'due_date' => now()->subMonth()->toDateString(),
        'principal_amount' => 6000,
        'interest_amount' => 60,
    ]);
    $this->loan->installments()->create([
        'sequence' => 2,
        'due_date' => now()->addMonth()->toDateString(),
        'principal_amount' => 6000,
        'interest_amount' => 30,
    ]);
    $this->bankConnection = BankConnection::factory()->create();
});

function assignToInstallment(int $installmentId, string $amount, bool $accepted = true): void
{
    LoanInstallmentTransaction::create([
        'loan_installment_id' => $installmentId,
        'transaction_id' => Transaction::factory()->create([
            'bank_connection_id' => test()->bankConnection->getKey(),
            'amount' => $amount,
        ])->getKey(),
        'amount' => $amount,
        'is_accepted' => $accepted,
    ]);
}

test('an assignment covering the full amount settles the installment', function (): void {
    assignToInstallment($this->installment->getKey(), '-6060');

    expect($this->loan->refresh()->remaining)->toEqual(6000)
        ->and($this->loan->installments()->settled()->count())->toBe(1);
});

test('a partial assignment does not settle the installment', function (): void {
    assignToInstallment($this->installment->getKey(), '-3000');

    expect($this->loan->refresh()->remaining)->toEqual(12000)
        ->and($this->loan->installments()->settled()->count())->toBe(0);
});

test('partial assignments add up until the installment is covered', function (): void {
    assignToInstallment($this->installment->getKey(), '-3000');
    assignToInstallment($this->installment->getKey(), '-3060');

    expect($this->loan->refresh()->remaining)->toEqual(6000);
});

test('an unaccepted assignment does not settle the installment', function (): void {
    assignToInstallment($this->installment->getKey(), '-6060', accepted: false);

    expect($this->loan->refresh()->remaining)->toEqual(12000);
});

test('a returned direct debit reopens the installment', function (): void {
    assignToInstallment($this->installment->getKey(), '-6060');
    assignToInstallment($this->installment->getKey(), '6060');

    expect($this->loan->refresh()->remaining)->toEqual(12000)
        ->and($this->loan->installments()->settled()->count())->toBe(0);
});

test('a manually paid installment counts as settled', function (): void {
    $this->installment->update(['is_paid' => true]);

    expect($this->loan->refresh()->remaining)->toEqual(6000)
        ->and($this->loan->installments()->settled()->count())->toBe(1);
});

test('an unsettled installment past its due date is overdue', function (): void {
    expect($this->loan->installments()->overdue()->count())->toBe(1);

    assignToInstallment($this->installment->getKey(), '-6060');

    expect($this->loan->installments()->overdue()->count())->toBe(0);
});

test('deleting an assignment reopens the installment', function (): void {
    assignToInstallment($this->installment->getKey(), '-6060');

    expect($this->loan->refresh()->remaining)->toEqual(6000);

    LoanInstallmentTransaction::query()->first()->delete();

    expect($this->loan->refresh()->remaining)->toEqual(12000);
});

test('an accepted assignment lowers the balance of its transaction', function (): void {
    $transaction = Transaction::factory()->create([
        'bank_connection_id' => $this->bankConnection->getKey(),
        'amount' => -6060,
    ]);

    LoanInstallmentTransaction::create([
        'loan_installment_id' => $this->installment->getKey(),
        'transaction_id' => $transaction->getKey(),
        'amount' => -6060,
        'is_accepted' => true,
    ]);

    expect($transaction->refresh()->balance)->toEqual(0);
});

test('a suggestion leaves the balance of its transaction alone', function (): void {
    $transaction = Transaction::factory()->create([
        'bank_connection_id' => $this->bankConnection->getKey(),
        'amount' => -6060,
    ]);

    LoanInstallmentTransaction::create([
        'loan_installment_id' => $this->installment->getKey(),
        'transaction_id' => $transaction->getKey(),
        'amount' => -6060,
        'is_accepted' => false,
    ]);

    expect($transaction->refresh()->balance)->toEqual(-6060);
});

test('the total amount of an installment is principal plus interest', function (): void {
    expect(app(LoanInstallment::class)->query()->whereKey($this->installment->getKey())->first()->getTotalAmount())
        ->toBe('6060.00');
});
