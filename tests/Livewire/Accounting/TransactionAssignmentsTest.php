<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Accounting\TransactionAssignments;
use FluxErp\Models\Address;
use FluxErp\Models\BankConnection;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Loan;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Pivots\LoanInstallmentTransaction;
use FluxErp\Models\Pivots\OrderTransaction;
use FluxErp\Models\PriceList;
use FluxErp\Models\Tenant;
use FluxErp\Models\Transaction;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(TransactionAssignments::class)
        ->assertOk();
});

test('assign orders from selectedOrders property', function (): void {
    $bankConnection = BankConnection::factory()->create();
    $transaction = Transaction::factory()->create([
        'bank_connection_id' => $bankConnection->getKey(),
    ]);

    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);
    // The factory picks a random order type enum, and transactions cannot be assigned
    // to subscription orders, so pin a regular order type here.
    $orderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
    ]);
    $order = Order::factory()->create([
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'tenant_id' => Tenant::default()->getKey(),
        'language_id' => Language::default()->getKey(),
        'price_list_id' => PriceList::default()->getKey(),
        'payment_type_id' => PaymentType::default()->getKey(),
        'currency_id' => Currency::default()->getKey(),
        'order_type_id' => $orderType->getKey(),
        'balance' => 100,
    ]);

    Livewire::test(TransactionAssignments::class)
        ->call('assignOrdersModal', $transaction)
        ->set('selectedOrders', [$order->getKey()])
        ->call('assignOrders')
        ->assertOk();

    expect(OrderTransaction::query()
        ->where('transaction_id', $transaction->getKey())
        ->where('order_id', $order->getKey())
        ->exists()
    )->toBeTrue();
});

test('gotoPage updates page and dispatches refresh', function (): void {
    Livewire::test(TransactionAssignments::class)
        ->call('gotoPage', 3)
        ->assertSet('paginators.page', 3)
        ->assertDispatched('refresh-transactions');
});

test('updating perPage resets the page and dispatches refresh', function (): void {
    Livewire::test(TransactionAssignments::class)
        ->call('gotoPage', 3)
        ->set('perPage', 50)
        ->assertSet('perPage', 50)
        ->assertSet('paginators.page', 1)
        ->assertDispatched('refresh-transactions');
});

test('editOrderTransaction populates form with order gross total and balance', function (): void {
    $bankConnection = BankConnection::factory()->create();
    $transaction = Transaction::factory()->create([
        'bank_connection_id' => $bankConnection->getKey(),
    ]);

    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);
    $orderType = OrderType::factory()->create();
    $order = Order::factory()->create([
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'tenant_id' => Tenant::default()->getKey(),
        'language_id' => Language::default()->getKey(),
        'price_list_id' => PriceList::default()->getKey(),
        'payment_type_id' => PaymentType::default()->getKey(),
        'currency_id' => Currency::default()->getKey(),
        'order_type_id' => $orderType->getKey(),
        'total_gross_price' => 250,
        'balance' => 100,
    ]);

    $orderTransaction = OrderTransaction::query()->create([
        'transaction_id' => $transaction->getKey(),
        'order_id' => $order->getKey(),
        'amount' => 50,
        'is_accepted' => false,
    ]);

    Livewire::test(TransactionAssignments::class)
        ->call('editOrderTransaction', $orderTransaction->getAttribute('pivot_id'))
        ->assertSet('orderTransactionForm.orderGrossTotal', 250.0)
        ->assertSet('orderTransactionForm.orderBalance', 100.0);
});

test('editOrderTransaction populates the transaction payment amount', function (): void {
    $bankConnection = BankConnection::factory()->create();
    $transaction = Transaction::factory()->create([
        'bank_connection_id' => $bankConnection->getKey(),
        'amount' => 175.5,
    ]);

    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);
    $orderType = OrderType::factory()->create();
    $order = Order::factory()->create([
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'tenant_id' => Tenant::default()->getKey(),
        'language_id' => Language::default()->getKey(),
        'price_list_id' => PriceList::default()->getKey(),
        'payment_type_id' => PaymentType::default()->getKey(),
        'currency_id' => Currency::default()->getKey(),
        'order_type_id' => $orderType->getKey(),
        'total_gross_price' => 250,
        'balance' => 100,
    ]);

    $orderTransaction = OrderTransaction::query()->create([
        'transaction_id' => $transaction->getKey(),
        'order_id' => $order->getKey(),
        'amount' => 50,
        'is_accepted' => false,
    ]);

    Livewire::test(TransactionAssignments::class)
        ->call('editOrderTransaction', $orderTransaction->getAttribute('pivot_id'))
        ->assertSet('orderTransactionForm.transactionAmount', 175.5);
});

test('assign ledger account through the modal', function (): void {
    $bankConnection = BankConnection::factory()->create();
    $transaction = Transaction::factory()->create([
        'bank_connection_id' => $bankConnection->getKey(),
        'amount' => 3000,
        'balance' => 3000,
    ]);
    $ledgerAccount = LedgerAccount::factory()->create([
        'tenant_id' => Tenant::default()->getKey(),
        'ledger_account_type_enum' => FluxErp\Enums\LedgerAccountTypeEnum::Expense,
    ]);

    Livewire::test(TransactionAssignments::class)
        ->call('assignLedgerAccountModal', $transaction)
        ->assertSet('ledgerAccountTransactionForm.transaction_id', $transaction->getKey())
        ->assertSet('ledgerAccountTransactionForm.amount', 3000.0)
        ->set('ledgerAccountTransactionForm.ledger_account_id', $ledgerAccount->getKey())
        ->call('saveLedgerAccountTransaction')
        ->assertOk()
        ->assertHasNoErrors();

    expect(FluxErp\Models\Pivots\LedgerAccountTransaction::query()
        ->where('ledger_account_id', $ledgerAccount->getKey())
        ->where('transaction_id', $transaction->getKey())
        ->exists()
    )->toBeTrue()
        ->and((float) $transaction->fresh()->balance)->toBe(0.0);
});

test('assignLedgerAccountModal prefills the transaction balance for the apply button', function (): void {
    $bankConnection = BankConnection::factory()->create();
    $transaction = Transaction::factory()->create([
        'bank_connection_id' => $bankConnection->getKey(),
        'amount' => -4250,
        'balance' => -4250,
    ]);

    Livewire::test(TransactionAssignments::class)
        ->call('assignLedgerAccountModal', $transaction)
        ->assertSet('ledgerAccountTransactionForm.amount', -4250.0)
        ->assertSet('ledgerAccountTransactionForm.transactionBalance', -4250.0);
});

test('opening the ledger account modal resets a previous validation error', function (): void {
    $bankConnection = BankConnection::factory()->create();
    $transaction = Transaction::factory()->create([
        'bank_connection_id' => $bankConnection->getKey(),
        'amount' => 100,
        'balance' => 100,
    ]);

    Livewire::test(TransactionAssignments::class)
        ->call('saveLedgerAccountTransaction')
        ->assertHasErrors()
        ->call('assignLedgerAccountModal', $transaction)
        ->assertHasNoErrors();
});

test('saveAttachment stores a single attachment on the transaction', function (): void {
    $transaction = Transaction::factory()->create([
        'bank_connection_id' => BankConnection::factory()->create()->getKey(),
    ]);

    Livewire::test(TransactionAssignments::class)
        ->call('attachmentModal', $transaction)
        ->set('attachment.file', [UploadedFile::fake()->image('receipt.jpg')])
        ->call('saveAttachment')
        ->assertHasNoErrors();

    expect($transaction->refresh()->getFirstMedia('attachment')?->file_name)->toBe('receipt.jpg');
});

test('uploading a second attachment replaces the first one', function (): void {
    $transaction = Transaction::factory()->create([
        'bank_connection_id' => BankConnection::factory()->create()->getKey(),
    ]);

    Livewire::test(TransactionAssignments::class)
        ->call('attachmentModal', $transaction)
        ->set('attachment.file', [UploadedFile::fake()->image('first.jpg')])
        ->call('saveAttachment')
        ->assertHasNoErrors()
        ->call('attachmentModal', $transaction->refresh())
        ->set('attachment.file', [UploadedFile::fake()->image('second.jpg')])
        ->call('saveAttachment')
        ->assertHasNoErrors();

    $media = $transaction->refresh()->getMedia('attachment');

    expect($media)->toHaveCount(1)
        ->and($media->first()->file_name)->toBe('second.jpg');
});

test('replacing a staged file before saving stores only the newly picked file', function (): void {
    $transaction = Transaction::factory()->create([
        'bank_connection_id' => BankConnection::factory()->create()->getKey(),
    ]);

    Livewire::test(TransactionAssignments::class)
        ->call('attachmentModal', $transaction)
        ->set('attachment.file', [UploadedFile::fake()->image('wrong.jpg')])
        ->set('attachment.stagedFiles.0.shouldDelete', true)
        ->set('attachment.file', [UploadedFile::fake()->image('right.jpg')])
        ->call('saveAttachment')
        ->assertHasNoErrors();

    $media = $transaction->refresh()->getMedia('attachment');

    expect($media)->toHaveCount(1)
        ->and($media->first()->file_name)->toBe('right.jpg');
});

test('a loan installment can be assigned, accepted and detached', function (): void {
    $loan = Loan::factory()->create([
        'tenant_id' => Tenant::default()->getKey(),
        'contact_id' => Contact::factory()->create()->getKey(),
        'ledger_account_id' => LedgerAccount::factory()
            ->create(['tenant_id' => Tenant::default()->getKey()])
            ->getKey(),
        'amount' => 1000,
        'number_of_installments' => 1,
    ]);
    $installment = $loan->installments()->create([
        'sequence' => 1,
        'due_date' => now()->toDateString(),
        'principal_amount' => 1000,
        'interest_amount' => 10,
    ]);

    $transaction = Transaction::factory()->create([
        'bank_connection_id' => BankConnection::factory()->create()->getKey(),
        'amount' => -1010,
        'balance' => -1010,
    ]);

    $component = Livewire::test(TransactionAssignments::class)
        ->call('assignLoanInstallmentModal', $transaction)
        ->set('loanInstallmentTransactionForm.loan_installment_id', $installment->getKey())
        ->set('loanInstallmentTransactionForm.amount', -1010)
        ->set('loanInstallmentTransactionForm.is_accepted', false)
        ->call('saveLoanInstallmentTransaction')
        ->assertHasNoErrors();

    $pivot = LoanInstallmentTransaction::query()
        ->where('transaction_id', $transaction->getKey())
        ->firstOrFail();

    expect($pivot->is_accepted)->toBeFalse()
        ->and($loan->refresh()->remaining)->toEqual(1000);

    $component->call('acceptAll', $transaction)
        ->assertHasNoErrors();

    expect($pivot->refresh()->is_accepted)->toBeTrue()
        ->and($loan->refresh()->remaining)->toEqual(0);

    $component->call('deleteLoanInstallmentTransaction', $pivot)
        ->assertHasNoErrors();

    expect(LoanInstallmentTransaction::query()->whereKey($pivot->getKey())->exists())->toBeFalse()
        ->and($loan->refresh()->remaining)->toEqual(1000);
});

test('a ledger account error stays off the sibling transaction forms', function (): void {
    Livewire::test(TransactionAssignments::class)
        ->call('saveLedgerAccountTransaction')
        ->assertHasErrors('ledgerAccountTransactionForm.amount')
        ->assertHasNoErrors([
            'loanInstallmentTransactionForm.amount',
            'orderTransactionForm.amount',
            'transactionForm.amount',
        ]);
});

function createTransactionAssignmentsOrder(int $currencyId, float $balance): Order
{
    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);

    return Order::factory()->create([
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'tenant_id' => Tenant::default()->getKey(),
        'language_id' => Language::default()->getKey(),
        'price_list_id' => PriceList::default()->getKey(),
        'payment_type_id' => PaymentType::default()->getKey(),
        'currency_id' => $currencyId,
        'order_type_id' => OrderType::factory()
            ->create([
                'order_type_enum' => OrderTypeEnum::Order,
                'is_active' => true,
            ])
            ->getKey(),
        'balance' => $balance,
    ]);
}

function createTransactionAssignmentsTransaction(float $amount): Transaction
{
    return Transaction::factory()->create([
        'bank_connection_id' => BankConnection::factory()->create()->getKey(),
        'currency_id' => Currency::default()->getKey(),
        'amount' => $amount,
        'balance' => $amount,
    ]);
}

test('assigning several foreign currency orders splits the remaining transaction amount', function (): void {
    $transaction = createTransactionAssignmentsTransaction(859.38);
    $usd = Currency::factory()->create(['iso' => 'USD', 'is_default' => false]);

    $euroOrder = createTransactionAssignmentsOrder(Currency::default()->getKey(), 300);
    $smallUsdOrder = createTransactionAssignmentsOrder($usd->getKey(), 23.74);
    $largeUsdOrder = createTransactionAssignmentsOrder($usd->getKey(), 127.96);

    Livewire::test(TransactionAssignments::class)
        ->call('assignOrdersModal', $transaction)
        ->set('selectedOrders', [$smallUsdOrder->getKey(), $euroOrder->getKey(), $largeUsdOrder->getKey()])
        ->call('assignOrders')
        ->assertOk();

    $amounts = OrderTransaction::query()
        ->where('transaction_id', $transaction->getKey())
        ->pluck('amount', 'order_id');

    expect($amounts)->toHaveCount(3)
        ->and(bcround($amounts->sum(), 2))->toEqual('859.38')
        ->and(bcround($amounts[$euroOrder->getKey()], 2))->toEqual('300.00')
        ->and(bcround($amounts[$smallUsdOrder->getKey()], 2))->toEqual('87.54')
        ->and(bcround($amounts[$largeUsdOrder->getKey()], 2))->toEqual('471.84');
});

test('foreign currency orders get no suggestion once the transaction amount is used up', function (): void {
    $transaction = createTransactionAssignmentsTransaction(100);
    $usd = Currency::factory()->create(['iso' => 'USD', 'is_default' => false]);

    $euroOrder = createTransactionAssignmentsOrder(Currency::default()->getKey(), 120);
    $usdOrder = createTransactionAssignmentsOrder($usd->getKey(), 50);

    Livewire::test(TransactionAssignments::class)
        ->call('assignOrdersModal', $transaction)
        ->set('selectedOrders', [$euroOrder->getKey(), $usdOrder->getKey()])
        ->call('assignOrders')
        ->assertOk();

    expect(OrderTransaction::query()
        ->where('transaction_id', $transaction->getKey())
        ->pluck('order_id')
        ->all()
    )->toEqual([$euroOrder->getKey()]);
});

test('a pending ledger account suggestion reduces the amount left for foreign currency orders', function (): void {
    $transaction = createTransactionAssignmentsTransaction(100);
    $usd = Currency::factory()->create(['iso' => 'USD', 'is_default' => false]);
    $usdOrder = createTransactionAssignmentsOrder($usd->getKey(), 50);

    FluxErp\Models\Pivots\LedgerAccountTransaction::factory()->create([
        'ledger_account_id' => LedgerAccount::factory()
            ->create([
                'tenant_id' => Tenant::default()->getKey(),
                'ledger_account_type_enum' => FluxErp\Enums\LedgerAccountTypeEnum::Expense,
            ])
            ->getKey(),
        'transaction_id' => $transaction->getKey(),
        'amount' => 40,
        'is_accepted' => false,
    ]);

    Livewire::test(TransactionAssignments::class)
        ->call('assignOrdersModal', $transaction)
        ->set('selectedOrders', [$usdOrder->getKey()])
        ->call('assignOrders')
        ->assertOk();

    expect(bcround(
        OrderTransaction::query()
            ->where('transaction_id', $transaction->getKey())
            ->where('order_id', $usdOrder->getKey())
            ->value('amount'),
        2
    ))->toEqual('60.00');
});

test('an order is assigned no more than the remaining transaction amount', function (): void {
    $transaction = createTransactionAssignmentsTransaction(80);
    $order = createTransactionAssignmentsOrder(Currency::default()->getKey(), 100);

    Livewire::test(TransactionAssignments::class)
        ->call('assignOrdersModal', $transaction)
        ->set('selectedOrders', [$order->getKey()])
        ->call('assignOrders')
        ->assertOk();

    expect(bcround(
        OrderTransaction::query()
            ->where('transaction_id', $transaction->getKey())
            ->where('order_id', $order->getKey())
            ->value('amount'),
        2
    ))->toEqual('80.00')
        ->and(bcround($transaction->refresh()->balance, 2))->toEqual('0.00');
});

test('several orders share the remaining transaction amount', function (): void {
    $transaction = createTransactionAssignmentsTransaction(150);
    $firstOrder = createTransactionAssignmentsOrder(Currency::default()->getKey(), 100);
    $secondOrder = createTransactionAssignmentsOrder(Currency::default()->getKey(), 100);

    Livewire::test(TransactionAssignments::class)
        ->call('assignOrdersModal', $transaction)
        ->set('selectedOrders', [$firstOrder->getKey(), $secondOrder->getKey()])
        ->call('assignOrders')
        ->assertOk();

    $amounts = OrderTransaction::query()
        ->where('transaction_id', $transaction->getKey())
        ->pluck('amount', 'order_id');

    expect(bcround($amounts[$firstOrder->getKey()], 2))->toEqual('100.00')
        ->and(bcround($amounts[$secondOrder->getKey()], 2))->toEqual('50.00');
});

test('an order smaller than the transaction is assigned its full balance', function (): void {
    $transaction = createTransactionAssignmentsTransaction(500);
    $order = createTransactionAssignmentsOrder(Currency::default()->getKey(), 100);

    Livewire::test(TransactionAssignments::class)
        ->call('assignOrdersModal', $transaction)
        ->set('selectedOrders', [$order->getKey()])
        ->call('assignOrders')
        ->assertOk();

    expect(bcround(
        OrderTransaction::query()
            ->where('transaction_id', $transaction->getKey())
            ->value('amount'),
        2
    ))->toEqual('100.00');
});

test('a credit note is netted before the invoices are capped', function (): void {
    $transaction = createTransactionAssignmentsTransaction(100);
    $invoice = createTransactionAssignmentsOrder(Currency::default()->getKey(), 200);
    $creditNote = createTransactionAssignmentsOrder(Currency::default()->getKey(), -50);

    Livewire::test(TransactionAssignments::class)
        ->call('assignOrdersModal', $transaction)
        ->set('selectedOrders', [$invoice->getKey(), $creditNote->getKey()])
        ->call('assignOrders')
        ->assertOk();

    $amounts = OrderTransaction::query()
        ->where('transaction_id', $transaction->getKey())
        ->pluck('amount', 'order_id');

    expect(bcround($amounts[$invoice->getKey()], 2))->toEqual('150.00')
        ->and(bcround($amounts[$creditNote->getKey()], 2))->toEqual('-50.00')
        ->and(bcround($transaction->refresh()->balance, 2))->toEqual('0.00');
});

test('no order is assigned once the transaction amount is used up', function (): void {
    $transaction = createTransactionAssignmentsTransaction(100);
    $firstOrder = createTransactionAssignmentsOrder(Currency::default()->getKey(), 100);
    $secondOrder = createTransactionAssignmentsOrder(Currency::default()->getKey(), 40);

    Livewire::test(TransactionAssignments::class)
        ->call('assignOrdersModal', $transaction)
        ->set('selectedOrders', [$firstOrder->getKey(), $secondOrder->getKey()])
        ->call('assignOrders')
        ->assertOk();

    expect(OrderTransaction::query()
        ->where('transaction_id', $transaction->getKey())
        ->pluck('order_id')
        ->all()
    )->toEqual([$firstOrder->getKey()]);
});

test('an order that cannot be assigned leaves the remaining amount for the next order', function (): void {
    $transaction = createTransactionAssignmentsTransaction(100);
    $subscriptionOrder = createTransactionAssignmentsOrder(Currency::default()->getKey(), 100);
    $subscriptionOrder->orderType->update(['order_type_enum' => OrderTypeEnum::Subscription]);
    $order = createTransactionAssignmentsOrder(Currency::default()->getKey(), 100);

    Livewire::test(TransactionAssignments::class)
        ->call('assignOrdersModal', $transaction)
        ->set('selectedOrders', [$subscriptionOrder->getKey(), $order->getKey()])
        ->call('assignOrders')
        ->assertOk();

    expect(OrderTransaction::query()
        ->where('transaction_id', $transaction->getKey())
        ->pluck('amount', 'order_id')
        ->map(fn (string $amount) => bcround($amount, 2))
        ->all()
    )->toEqual([$order->getKey() => '100.00']);
});

test('orders skipped because nothing is left show a warning', function (): void {
    $transaction = createTransactionAssignmentsTransaction(100);
    OrderTransaction::query()->create([
        'transaction_id' => $transaction->getKey(),
        'order_id' => createTransactionAssignmentsOrder(Currency::default()->getKey(), 100)->getKey(),
        'amount' => 100,
        'is_accepted' => false,
    ]);
    $order = createTransactionAssignmentsOrder(Currency::default()->getKey(), 50);

    Livewire::test(TransactionAssignments::class)
        ->call('assignOrdersModal', $transaction)
        ->set('selectedOrders', [$order->getKey()])
        ->call('assignOrders')
        ->assertOk()
        ->assertToastNotification(type: 'warning');

    expect(OrderTransaction::query()
        ->where('order_id', $order->getKey())
        ->exists()
    )->toBeFalse();
});

test('assigning orders to a deleted transaction shows an error and assigns nothing', function (): void {
    $transaction = createTransactionAssignmentsTransaction(80);
    $order = createTransactionAssignmentsOrder(Currency::default()->getKey(), 100);

    $component = Livewire::test(TransactionAssignments::class)
        ->call('assignOrdersModal', $transaction)
        ->set('selectedOrders', [$order->getKey()]);

    $transaction->delete();

    $component->call('assignOrders')
        ->assertOk()
        ->assertToastNotification(type: 'error')
        ->assertSet('selectedOrders', []);

    expect(OrderTransaction::query()->where('order_id', $order->getKey())->exists())->toBeFalse();
});

test('an order without balance does not claim the transaction amount is used up', function (): void {
    $transaction = createTransactionAssignmentsTransaction(100);
    $order = createTransactionAssignmentsOrder(Currency::default()->getKey(), 0);

    Livewire::test(TransactionAssignments::class)
        ->call('assignOrdersModal', $transaction)
        ->set('selectedOrders', [$order->getKey()])
        ->call('assignOrders')
        ->assertOk()
        ->assertNotDispatched(
            'ts-ui:toast',
            fn (string $eventName, array $params) => data_get($params, 'type') === 'warning'
        );
});

test('an invoice and a credit note that cancel out are assigned when nothing is left', function (): void {
    $transaction = createTransactionAssignmentsTransaction(100);
    OrderTransaction::query()->create([
        'transaction_id' => $transaction->getKey(),
        'order_id' => createTransactionAssignmentsOrder(Currency::default()->getKey(), 100)->getKey(),
        'amount' => 100,
        'is_accepted' => false,
    ]);
    $invoice = createTransactionAssignmentsOrder(Currency::default()->getKey(), 100);
    $creditNote = createTransactionAssignmentsOrder(Currency::default()->getKey(), -100);

    Livewire::test(TransactionAssignments::class)
        ->call('assignOrdersModal', $transaction)
        ->set('selectedOrders', [$invoice->getKey(), $creditNote->getKey()])
        ->call('assignOrders')
        ->assertOk();

    $amounts = OrderTransaction::query()
        ->where('transaction_id', $transaction->getKey())
        ->pluck('amount', 'order_id');

    expect(bcround($amounts[$invoice->getKey()], 2))->toEqual('100.00')
        ->and(bcround($amounts[$creditNote->getKey()], 2))->toEqual('-100.00');
});

test('an invoice is not assigned when pending suggestions exceed the transaction amount', function (): void {
    $transaction = createTransactionAssignmentsTransaction(100);

    foreach ([1, 2] as $ignored) {
        OrderTransaction::query()->create([
            'transaction_id' => $transaction->getKey(),
            'order_id' => createTransactionAssignmentsOrder(Currency::default()->getKey(), 100)->getKey(),
            'amount' => 100,
            'is_accepted' => false,
        ]);
    }

    $invoice = createTransactionAssignmentsOrder(Currency::default()->getKey(), 200);

    Livewire::test(TransactionAssignments::class)
        ->call('assignOrdersModal', $transaction)
        ->set('selectedOrders', [$invoice->getKey()])
        ->call('assignOrders')
        ->assertOk()
        ->assertToastNotification(type: 'warning');

    expect(OrderTransaction::query()->where('order_id', $invoice->getKey())->exists())->toBeFalse();
});
