<?php

use FluxErp\Actions\LoanInstallmentTransaction\CreateLoanInstallmentTransaction;
use FluxErp\Actions\LoanInstallmentTransaction\DeleteLoanInstallmentTransaction;
use FluxErp\Actions\LoanInstallmentTransaction\UpdateLoanInstallmentTransaction;
use FluxErp\Models\BankConnection;
use FluxErp\Models\Contact;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Loan;
use FluxErp\Models\Transaction;
use Illuminate\Validation\ValidationException;

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
    $this->transaction = Transaction::factory()->create([
        'bank_connection_id' => BankConnection::factory()->create()->getKey(),
        'amount' => -6060,
    ]);
});

test('create assigns a transaction to an installment', function (): void {
    $assignment = CreateLoanInstallmentTransaction::make([
        'loan_installment_id' => $this->installment->getKey(),
        'transaction_id' => $this->transaction->getKey(),
        'amount' => -6060,
        'is_accepted' => true,
    ])
        ->validate()
        ->execute();

    expect($assignment->loan_installment_id)->toBe($this->installment->getKey())
        ->and($this->loan->refresh()->remaining)->toEqual(6000);
});

test('create leaves the installment open while the assignment is a suggestion', function (): void {
    CreateLoanInstallmentTransaction::make([
        'loan_installment_id' => $this->installment->getKey(),
        'transaction_id' => $this->transaction->getKey(),
        'amount' => -6060,
    ])
        ->validate()
        ->execute();

    expect($this->loan->refresh()->remaining)->toEqual(12000);
});

test('create rejects an unknown installment', function (): void {
    CreateLoanInstallmentTransaction::make([
        'loan_installment_id' => $this->installment->getKey() + 100,
        'transaction_id' => $this->transaction->getKey(),
        'amount' => -6060,
    ])->validate();
})->throws(ValidationException::class);

test('create rejects an unknown transaction', function (): void {
    CreateLoanInstallmentTransaction::make([
        'loan_installment_id' => $this->installment->getKey(),
        'transaction_id' => $this->transaction->getKey() + 100,
        'amount' => -6060,
    ])->validate();
})->throws(ValidationException::class);

test('accepting a suggestion settles the installment', function (): void {
    $assignment = CreateLoanInstallmentTransaction::make([
        'loan_installment_id' => $this->installment->getKey(),
        'transaction_id' => $this->transaction->getKey(),
        'amount' => -6060,
    ])
        ->validate()
        ->execute();

    expect($this->loan->refresh()->remaining)->toEqual(12000);

    UpdateLoanInstallmentTransaction::make([
        'pivot_id' => $assignment->pivot_id,
        'is_accepted' => true,
    ])
        ->validate()
        ->execute();

    expect($this->loan->refresh()->remaining)->toEqual(6000);
});

test('delete reopens the installment', function (): void {
    $assignment = CreateLoanInstallmentTransaction::make([
        'loan_installment_id' => $this->installment->getKey(),
        'transaction_id' => $this->transaction->getKey(),
        'amount' => -6060,
        'is_accepted' => true,
    ])
        ->validate()
        ->execute();

    expect($this->loan->refresh()->remaining)->toEqual(6000);

    DeleteLoanInstallmentTransaction::make(['pivot_id' => $assignment->pivot_id])
        ->validate()
        ->execute();

    expect($this->loan->refresh()->remaining)->toEqual(12000)
        ->and($this->transaction->refresh()->balance)->toEqual(-6060);
});
