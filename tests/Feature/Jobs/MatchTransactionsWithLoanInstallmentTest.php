<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Jobs\Accounting\MatchTransactionsWithOrderJob;
use FluxErp\Models\Address;
use FluxErp\Models\BankConnection;
use FluxErp\Models\Contact;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Models\Currency;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Loan;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Transaction;
use FluxErp\Settings\AccountingSettings;

beforeEach(function (): void {
    $this->creditorIban = 'DE02120300000000202051';

    $this->contact = Contact::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create();
    ContactBankConnection::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'iban' => $this->creditorIban,
    ]);

    $this->loan = Loan::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $this->contact->getKey(),
        'ledger_account_id' => LedgerAccount::factory()
            ->create(['tenant_id' => $this->dbTenant->getKey()])
            ->getKey(),
        'name' => 'Machine financing',
        'number' => 'LOAN-2026-001',
        'amount' => 12000,
        'number_of_installments' => 2,
    ]);
    $this->installment = $this->loan->installments()->create([
        'sequence' => 1,
        'due_date' => now()->subDays(2)->toDateString(),
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

function repayment(array $attributes = []): Transaction
{
    return Transaction::factory()->create(array_merge([
        'bank_connection_id' => test()->bankConnection->getKey(),
        'amount' => -6060,
        'balance' => -6060,
        'booking_date' => now()->subDay()->toDateString(),
        'counterpart_iban' => test()->creditorIban,
        'purpose' => 'Repayment',
        'is_ignored' => false,
    ], $attributes));
}

test('a transaction from the creditor iban is matched to the open installment', function (): void {
    $transaction = repayment();

    (new MatchTransactionsWithOrderJob([$transaction->getKey()]))->handle();

    expect($transaction->loanInstallments()->count())->toBe(1)
        ->and($transaction->loanInstallments()->first()->getKey())->toBe($this->installment->getKey());
});

test('the loan number in the purpose is enough to match', function (): void {
    $transaction = repayment([
        'counterpart_iban' => null,
        'purpose' => 'Rate 1 LOAN-2026-001',
    ]);

    (new MatchTransactionsWithOrderJob([$transaction->getKey()]))->handle();

    expect($transaction->loanInstallments()->count())->toBe(1);
});

test('a match stays a suggestion while the setting is off', function (): void {
    $transaction = repayment();

    (new MatchTransactionsWithOrderJob([$transaction->getKey()]))->handle();

    expect($transaction->loanInstallmentTransactions()->value('is_accepted'))->toBeFalse()
        ->and($this->loan->refresh()->remaining)->toEqual(12000);
});

test('a secure match is accepted on its own', function (): void {
    app(AccountingSettings::class)->fill(['auto_accept_secure_transaction_matches' => true])->save();

    $transaction = repayment();

    (new MatchTransactionsWithOrderJob([$transaction->getKey()]))->handle();

    expect($transaction->loanInstallmentTransactions()->value('is_accepted'))->toBeTrue()
        ->and($this->loan->refresh()->remaining)->toEqual(6000);
});

test('an amount that is not the installment amount is not matched on the iban alone', function (): void {
    $transaction = repayment(['amount' => -5000, 'balance' => -5000]);

    (new MatchTransactionsWithOrderJob([$transaction->getKey()]))->handle();

    expect($transaction->loanInstallments()->count())->toBe(0);
});

test('a partial payment naming the loan stays a suggestion', function (): void {
    app(AccountingSettings::class)->fill(['auto_accept_secure_transaction_matches' => true])->save();

    $transaction = repayment([
        'amount' => -5000,
        'balance' => -5000,
        'purpose' => 'Teilzahlung LOAN-2026-001',
    ]);

    (new MatchTransactionsWithOrderJob([$transaction->getKey()]))->handle();

    expect($transaction->loanInstallmentTransactions()->value('is_accepted'))->toBeFalse()
        ->and($transaction->loanInstallments()->count())->toBe(1);
});

test('a booking date far from the due date stays a suggestion', function (): void {
    app(AccountingSettings::class)->fill(['auto_accept_secure_transaction_matches' => true])->save();

    $transaction = repayment(['booking_date' => now()->subDays(40)->toDateString()]);

    (new MatchTransactionsWithOrderJob([$transaction->getKey()]))->handle();

    expect($transaction->loanInstallmentTransactions()->value('is_accepted'))->toBeFalse();
});

test('a settled installment is skipped in favour of the next open one', function (): void {
    $this->installment->update(['is_paid' => true]);

    $transaction = repayment(['purpose' => 'Rate 2 LOAN-2026-001']);

    (new MatchTransactionsWithOrderJob([$transaction->getKey()]))->handle();

    expect($transaction->loanInstallments()->pluck('sequence')->all())->toBe([2]);
});

test('a loan without an open installment is left alone', function (): void {
    $this->loan->installments()->update(['is_paid' => true]);

    $transaction = repayment();

    (new MatchTransactionsWithOrderJob([$transaction->getKey()]))->handle();

    expect($transaction->loanInstallments()->count())->toBe(0);
});

test('the loan wins over an order with the same balance', function (): void {
    $address = Address::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'is_main_address' => true,
        'is_invoice_address' => true,
    ]);

    Order::factory()->create([
        'order_type_id' => OrderType::factory()->create([
            'order_type_enum' => OrderTypeEnum::Order,
            'is_active' => true,
        ])->getKey(),
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $this->contact->getKey(),
        'payment_type_id' => PaymentType::factory()
            ->hasAttached($this->dbTenant, relationship: 'tenants')
            ->create()
            ->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
        'currency_id' => Currency::factory()->create()->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'invoice_number' => 'INV-2026-500',
        'invoice_date' => now()->subDays(10)->toDateString(),
        'balance' => -6060,
    ]);

    $transaction = repayment();

    (new MatchTransactionsWithOrderJob([$transaction->getKey()]))->handle();

    expect($transaction->loanInstallments()->count())->toBe(1)
        ->and($transaction->orders()->count())->toBe(0);
});

test('a transaction without iban and purpose is left alone', function (): void {
    $transaction = repayment(['counterpart_iban' => null, 'purpose' => null]);

    (new MatchTransactionsWithOrderJob([$transaction->getKey()]))->handle();

    expect($transaction->loanInstallments()->count())->toBe(0);
});

test('a second job run does not assign the same installment twice', function (): void {
    $transaction = repayment();

    (new MatchTransactionsWithOrderJob([$transaction->getKey()]))->handle();
    (new MatchTransactionsWithOrderJob([$transaction->getKey()]))->handle();

    expect($transaction->loanInstallmentTransactions()->count())->toBe(1);
});

test('a chargeback goes back to the installment it reverses', function (): void {
    $payment = repayment();
    $payment->loanInstallments()->attach($this->installment->getKey(), [
        'amount' => -6060,
        'is_accepted' => true,
    ]);

    $chargeback = repayment([
        'amount' => 6060,
        'balance' => 6060,
        'purpose' => 'Ruecklastschrift LOAN-2026-001',
        'booking_date' => now()->toDateString(),
    ]);

    (new MatchTransactionsWithOrderJob([$chargeback->getKey()]))->handle();

    expect($chargeback->loanInstallments()->pluck('sequence')->all())->toBe([1]);
});

test('an unrelated booking from the creditor iban is not matched', function (): void {
    $transaction = repayment([
        'amount' => -8.90,
        'balance' => -8.90,
        'purpose' => 'Kontofuehrungsgebuehr',
    ]);

    (new MatchTransactionsWithOrderJob([$transaction->getKey()]))->handle();

    expect($transaction->loanInstallments()->count())->toBe(0);
});
