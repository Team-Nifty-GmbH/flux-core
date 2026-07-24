<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Accounting\TransactionAssignments;
use FluxErp\Models\Address;
use FluxErp\Models\BankConnection;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
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
    $ledgerAccount = FluxErp\Models\LedgerAccount::factory()->create([
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
