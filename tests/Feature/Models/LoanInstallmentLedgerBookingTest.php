<?php

use FluxErp\Models\BankConnection;
use FluxErp\Models\Contact;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\LedgerBooking;
use FluxErp\Models\Loan;
use FluxErp\Models\Pivots\LoanInstallmentTransaction;
use FluxErp\Models\Transaction;

beforeEach(function (): void {
    $this->loanLedgerAccount = LedgerAccount::factory()->create(['tenant_id' => $this->dbTenant->getKey()]);
    $this->bankLedgerAccount = LedgerAccount::factory()->create(['tenant_id' => $this->dbTenant->getKey()]);

    $this->loan = Loan::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => Contact::factory()->create()->getKey(),
        'ledger_account_id' => $this->loanLedgerAccount->getKey(),
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
    $this->transaction = Transaction::factory()->create([
        'bank_connection_id' => $this->bankConnection->getKey(),
        'amount' => -6060,
        'balance' => -6060,
        'booking_date' => '2026-03-15',
        'is_ignored' => false,
    ]);
});

test('an accepted repayment books against the loan ledger account', function (): void {
    $assignment = LoanInstallmentTransaction::create([
        'loan_installment_id' => $this->installment->getKey(),
        'transaction_id' => $this->transaction->getKey(),
        'amount' => -6060,
        'is_accepted' => true,
    ]);

    $booking = $assignment->ledgerBookings()->first();

    expect($booking)->not->toBeNull()
        ->and($booking->debit_ledger_account_id)->toEqual($this->loanLedgerAccount->getKey())
        ->and($booking->credit_ledger_account_id)->toEqual($this->bankLedgerAccount->getKey())
        ->and($booking->amount)->toEqual(6060)
        ->and($booking->tenant_id)->toEqual($this->dbTenant->getKey())
        ->and($booking->booking_date->toDateString())->toEqual('2026-03-15');
});

test('an unaccepted repayment books nothing', function (): void {
    $assignment = LoanInstallmentTransaction::create([
        'loan_installment_id' => $this->installment->getKey(),
        'transaction_id' => $this->transaction->getKey(),
        'amount' => -6060,
        'is_accepted' => false,
    ]);

    expect($assignment->ledgerBookings()->exists())->toBeFalse();
});

test('un-accepting a repayment removes its booking', function (): void {
    $assignment = LoanInstallmentTransaction::create([
        'loan_installment_id' => $this->installment->getKey(),
        'transaction_id' => $this->transaction->getKey(),
        'amount' => -6060,
        'is_accepted' => true,
    ]);

    expect($assignment->ledgerBookings()->exists())->toBeTrue();

    $assignment->update(['is_accepted' => false]);

    expect($assignment->ledgerBookings()->exists())->toBeFalse();
});

test('deleting a repayment removes its booking', function (): void {
    $assignment = LoanInstallmentTransaction::create([
        'loan_installment_id' => $this->installment->getKey(),
        'transaction_id' => $this->transaction->getKey(),
        'amount' => -6060,
        'is_accepted' => true,
    ]);
    $bookingId = $assignment->ledgerBookings()->first()->getKey();

    $assignment->delete();

    expect(LedgerBooking::query()->whereKey($bookingId)->exists())->toBeFalse();
});

test('changing the amount updates the booking instead of adding one', function (): void {
    $assignment = LoanInstallmentTransaction::create([
        'loan_installment_id' => $this->installment->getKey(),
        'transaction_id' => $this->transaction->getKey(),
        'amount' => -6060,
        'is_accepted' => true,
    ]);
    $bookingId = $assignment->ledgerBookings()->first()->getKey();

    $assignment->update(['amount' => -3000]);

    expect(LedgerBooking::query()->where('source_id', $assignment->getKey())->count())->toEqual(1)
        ->and($assignment->ledgerBookings()->first())
        ->getKey()->toEqual($bookingId)
        ->amount->toEqual(3000);
});

test('a chargeback books the debt back', function (): void {
    $chargeback = Transaction::factory()->create([
        'bank_connection_id' => $this->bankConnection->getKey(),
        'amount' => 6060,
        'balance' => 6060,
        'booking_date' => '2026-04-01',
        'is_ignored' => false,
    ]);

    $assignment = LoanInstallmentTransaction::create([
        'loan_installment_id' => $this->installment->getKey(),
        'transaction_id' => $chargeback->getKey(),
        'amount' => 6060,
        'is_accepted' => true,
    ]);

    $booking = $assignment->ledgerBookings()->first();

    expect($booking->debit_ledger_account_id)->toEqual($this->bankLedgerAccount->getKey())
        ->and($booking->credit_ledger_account_id)->toEqual($this->loanLedgerAccount->getKey())
        ->and($booking->amount)->toEqual(6060);
});

test('a bank connection without a ledger account books nothing', function (): void {
    $transaction = Transaction::factory()->create([
        'bank_connection_id' => BankConnection::factory()->create(['ledger_account_id' => null])->getKey(),
        'amount' => -6060,
        'balance' => -6060,
        'is_ignored' => false,
    ]);

    $assignment = LoanInstallmentTransaction::create([
        'loan_installment_id' => $this->installment->getKey(),
        'transaction_id' => $transaction->getKey(),
        'amount' => -6060,
        'is_accepted' => true,
    ]);

    expect($assignment->ledgerBookings()->exists())->toBeFalse();
});
