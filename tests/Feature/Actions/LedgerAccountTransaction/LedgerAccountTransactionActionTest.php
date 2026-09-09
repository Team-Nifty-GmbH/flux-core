<?php

use FluxErp\Actions\LedgerAccountTransaction\CreateLedgerAccountTransaction;
use FluxErp\Actions\LedgerAccountTransaction\DeleteLedgerAccountTransaction;
use FluxErp\Actions\LedgerAccountTransaction\UpdateLedgerAccountTransaction;
use FluxErp\Actions\OrderTransaction\CreateOrderTransaction;
use FluxErp\Enums\LedgerAccountTypeEnum;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Pivots\LedgerAccountTransaction;
use FluxErp\Models\PriceList;
use FluxErp\Models\Transaction;

beforeEach(function (): void {
    $this->ledgerAccount = LedgerAccount::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'ledger_account_type_enum' => LedgerAccountTypeEnum::Expense,
    ]);
    $this->transaction = Transaction::factory()->create([
        'amount' => 203,
        'balance' => 203,
    ]);
});

test('create ledger account transaction', function (): void {
    $ledgerAccountTransaction = CreateLedgerAccountTransaction::make([
        'ledger_account_id' => $this->ledgerAccount->getKey(),
        'transaction_id' => $this->transaction->getKey(),
        'amount' => 3,
        'note' => 'Dunning fee',
    ])
        ->validate()
        ->execute();

    expect($ledgerAccountTransaction)->toBeInstanceOf(LedgerAccountTransaction::class)
        ->and($ledgerAccountTransaction->note)->toBe('Dunning fee')
        ->and($ledgerAccountTransaction->is_accepted)->toBeFalse();
});

test('create ledger account transaction requires ledger_account_id transaction_id amount', function (): void {
    CreateLedgerAccountTransaction::assertValidationErrors(
        [],
        ['ledger_account_id', 'transaction_id', 'amount']
    );
});

test('accepted ledger account transaction recalculates the transaction balance', function (): void {
    CreateLedgerAccountTransaction::make([
        'ledger_account_id' => $this->ledgerAccount->getKey(),
        'transaction_id' => $this->transaction->getKey(),
        'amount' => 203,
        'is_accepted' => true,
    ])
        ->validate()
        ->execute();

    expect((float) $this->transaction->fresh()->balance)->toBe(0.0);
});

test('unaccepted ledger account transaction does not change the transaction balance', function (): void {
    CreateLedgerAccountTransaction::make([
        'ledger_account_id' => $this->ledgerAccount->getKey(),
        'transaction_id' => $this->transaction->getKey(),
        'amount' => 203,
        'is_accepted' => false,
    ])
        ->validate()
        ->execute();

    expect((float) $this->transaction->fresh()->balance)->toBe(203.0);
});

test('update ledger account transaction recalculates the transaction balance', function (): void {
    $ledgerAccountTransaction = CreateLedgerAccountTransaction::make([
        'ledger_account_id' => $this->ledgerAccount->getKey(),
        'transaction_id' => $this->transaction->getKey(),
        'amount' => 3,
        'is_accepted' => true,
    ])
        ->validate()
        ->execute();

    UpdateLedgerAccountTransaction::make([
        'pivot_id' => $ledgerAccountTransaction->getKey(),
        'amount' => 103,
    ])
        ->validate()
        ->execute();

    expect((float) $this->transaction->fresh()->balance)->toBe(100.0);
});

test('delete ledger account transaction restores the transaction balance', function (): void {
    $ledgerAccountTransaction = CreateLedgerAccountTransaction::make([
        'ledger_account_id' => $this->ledgerAccount->getKey(),
        'transaction_id' => $this->transaction->getKey(),
        'amount' => 203,
        'is_accepted' => true,
    ])
        ->validate()
        ->execute();

    expect((float) $this->transaction->fresh()->balance)->toBe(0.0);

    DeleteLedgerAccountTransaction::make([
        'pivot_id' => $ledgerAccountTransaction->getKey(),
    ])
        ->validate()
        ->execute();

    expect((float) $this->transaction->fresh()->balance)->toBe(203.0);
});

test('mixed split between order and ledger account settles the transaction', function (): void {
    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);
    $orderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
    ]);
    $paymentType = PaymentType::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create();
    $order = Order::factory()->create([
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'currency_id' => Currency::factory()->create()->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'order_type_id' => $orderType->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'balance' => 200,
    ]);

    CreateOrderTransaction::make([
        'transaction_id' => $this->transaction->getKey(),
        'order_id' => $order->getKey(),
        'amount' => 200,
        'is_accepted' => true,
    ])
        ->validate()
        ->execute();

    expect((float) $this->transaction->fresh()->balance)->toBe(3.0);

    CreateLedgerAccountTransaction::make([
        'ledger_account_id' => $this->ledgerAccount->getKey(),
        'transaction_id' => $this->transaction->getKey(),
        'amount' => 3,
        'note' => 'Dunning fee',
        'is_accepted' => true,
    ])
        ->validate()
        ->execute();

    expect((float) $this->transaction->fresh()->balance)->toBe(0.0);
});
