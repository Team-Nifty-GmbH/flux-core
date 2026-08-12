<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Accounting\Loan;
use FluxErp\Livewire\Accounting\LoanLedgerBookings;
use FluxErp\Livewire\Accounting\LoanPayments;
use FluxErp\Models\Address;
use FluxErp\Models\BankConnection;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\LedgerBooking;
use FluxErp\Models\Loan as LoanModel;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Pivots\LoanInstallmentTransaction;
use FluxErp\Models\PriceList;
use FluxErp\Models\Transaction;
use FluxErp\States\Order\PaymentState\Paid;
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
})->with(['loan.general', 'loan.installments', 'loan.payments', 'loan.bookings', 'loan.documents']);

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
    $transaction = Transaction::factory()->create([
        'bank_connection_id' => BankConnection::factory()->create()->getKey(),
        'amount' => -6060,
        'purpose' => 'Rate 1 DARL-1',
    ]);

    LoanInstallmentTransaction::create([
        'loan_installment_id' => $this->loan->installments()->orderBy('sequence')->value('id'),
        'transaction_id' => $transaction->getKey(),
        'amount' => -6060,
        'is_accepted' => true,
    ]);

    $foreign = LoanModel::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $this->contact->getKey(),
        'ledger_account_id' => $this->ledgerAccount->getKey(),
        'amount' => 500,
        'number_of_installments' => 1,
    ]);
    $foreignInstallment = $foreign->installments()->create([
        'sequence' => 1,
        'due_date' => '2026-02-01',
        'principal_amount' => 500,
        'interest_amount' => 0,
    ]);
    LoanInstallmentTransaction::create([
        'loan_installment_id' => $foreignInstallment->getKey(),
        'transaction_id' => Transaction::factory()->create([
            'bank_connection_id' => BankConnection::factory()->create()->getKey(),
            'amount' => -500,
            'purpose' => 'Fremde Rate',
        ])->getKey(),
        'amount' => -500,
        'is_accepted' => true,
    ]);

    $data = Livewire::test(LoanPayments::class, ['loanId' => $this->loan->getKey()])
        ->call('loadData')
        ->assertOk()
        ->instance()
        ->getDataForTesting();

    expect(data_get($data, 'data'))->toHaveCount(1)
        ->and(data_get($data, 'data.0')['transaction.purpose'])->toEqual('Rate 1 DARL-1')
        ->and(data_get($data, 'data.0')['loan_installment.sequence'] ?? null)->not->toBeNull();
});

test('the totals count an installment settled by a transaction', function (): void {
    $transaction = Transaction::factory()->create([
        'bank_connection_id' => BankConnection::factory()->create()->getKey(),
        'amount' => -6060,
    ]);

    LoanInstallmentTransaction::create([
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
    $transaction = Transaction::factory()->create([
        'bank_connection_id' => BankConnection::factory()->create()->getKey(),
        'amount' => -6060,
    ]);

    LoanInstallmentTransaction::create([
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

test('can finance a purchase order from the loan', function (): void {
    $creditorAccount = LedgerAccount::factory()->create(['tenant_id' => $this->dbTenant->getKey()]);
    $address = Address::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'is_main_address' => true,
        'is_invoice_address' => true,
    ]);
    $orderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Purchase,
        'is_active' => true,
    ]);
    $order = Order::factory()->create([
        'order_type_id' => $orderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $this->contact->getKey(),
        'payment_type_id' => PaymentType::factory()
            ->hasAttached($this->dbTenant, relationship: 'tenants')
            ->create()
            ->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'currency_id' => Currency::default()->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'total_gross_price' => -2500,
        'balance' => -2500,
        'is_locked' => false,
    ]);

    Livewire::actingAs($this->user)
        ->test(Loan::class, ['id' => $this->loan->getKey()])
        ->set('financeOrder.order_id', $order->getKey())
        ->set('financeOrder.debit_ledger_account_id', $creditorAccount->getKey())
        ->set('financeOrder.amount', 2500)
        ->set('financeOrder.booking_date', '2026-09-01')
        ->call('finance')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('loan.order_id', $order->getKey());

    expect($this->loan->refresh()->order_id)->toBe($order->getKey())
        ->and($order->fresh()->payment_state)->toBeInstanceOf(Paid::class);
});

test('selecting an order prefills the creditor account and the open amount', function (): void {
    $creditorAccount = LedgerAccount::factory()->create(['tenant_id' => $this->dbTenant->getKey()]);
    $this->contact->update(['expense_ledger_account_id' => $creditorAccount->getKey()]);

    $address = Address::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'is_main_address' => true,
        'is_invoice_address' => true,
    ]);
    $orderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Purchase,
        'is_active' => true,
    ]);
    $order = Order::factory()->create([
        'order_type_id' => $orderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $this->contact->getKey(),
        'payment_type_id' => PaymentType::factory()
            ->hasAttached($this->dbTenant, relationship: 'tenants')
            ->create()
            ->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'currency_id' => Currency::default()->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'total_gross_price' => -1750.5,
        'balance' => -1750.5,
        'is_locked' => false,
    ]);

    Livewire::actingAs($this->user)
        ->test(Loan::class, ['id' => $this->loan->getKey()])
        ->call('changedFinancedOrder', $order->getKey())
        ->assertOk()
        ->assertSet('financeOrder.debit_ledger_account_id', $creditorAccount->getKey())
        ->assertSet('financeOrder.amount', 1750.5);
});

test('selecting a sales order is rejected right away', function (): void {
    $address = Address::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'is_main_address' => true,
        'is_invoice_address' => true,
    ]);
    $orderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
    ]);
    $order = Order::factory()->create([
        'order_type_id' => $orderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $this->contact->getKey(),
        'payment_type_id' => PaymentType::factory()
            ->hasAttached($this->dbTenant, relationship: 'tenants')
            ->create()
            ->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'currency_id' => Currency::default()->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'total_gross_price' => 500,
        'balance' => 500,
        'is_locked' => false,
    ]);

    Livewire::actingAs($this->user)
        ->test(Loan::class, ['id' => $this->loan->getKey()])
        ->set('financeOrder.order_id', $order->getKey())
        ->call('changedFinancedOrder', $order->getKey())
        ->assertOk()
        ->assertSet('financeOrder.order_id', null)
        ->assertSet('financeOrder.amount', null);
});

test('the bookings tab lists everything that touches the loan account', function (): void {
    $bankLedgerAccount = LedgerAccount::factory()->create(['tenant_id' => $this->dbTenant->getKey()]);
    $bankConnection = BankConnection::factory()->create([
        'ledger_account_id' => $bankLedgerAccount->getKey(),
    ]);
    $transaction = Transaction::factory()->create([
        'bank_connection_id' => $bankConnection->getKey(),
        'amount' => -6060,
        'balance' => -6060,
        'booking_date' => '2026-02-01',
        'is_ignored' => false,
    ]);

    LoanInstallmentTransaction::create([
        'loan_installment_id' => $this->loan->installments()->orderBy('sequence')->first()->getKey(),
        'transaction_id' => $transaction->getKey(),
        'amount' => -6060,
        'is_accepted' => true,
    ]);

    LedgerBooking::create([
        'tenant_id' => $this->dbTenant->getKey(),
        'debit_ledger_account_id' => $this->ledgerAccount->getKey(),
        'credit_ledger_account_id' => $bankLedgerAccount->getKey(),
        'amount' => 12000,
        'booking_date' => '2026-01-02',
        'booking_text' => 'Auszahlung',
    ]);

    $unrelated = LedgerBooking::create([
        'tenant_id' => $this->dbTenant->getKey(),
        'debit_ledger_account_id' => $bankLedgerAccount->getKey(),
        'credit_ledger_account_id' => LedgerAccount::factory()
            ->create(['tenant_id' => $this->dbTenant->getKey()])
            ->getKey(),
        'amount' => 99,
        'booking_date' => '2026-01-03',
        'booking_text' => 'Fremde Buchung',
    ]);

    $data = Livewire::test(LoanLedgerBookings::class, ['loanId' => $this->loan->getKey()])
        ->call('loadData')
        ->assertOk()
        ->instance()
        ->getDataForTesting();

    expect(collect(data_get($data, 'data'))->pluck('id'))
        ->toHaveCount(2)
        ->not->toContain($unrelated->getKey());
});
