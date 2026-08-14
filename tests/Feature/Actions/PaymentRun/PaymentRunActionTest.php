<?php

use FluxErp\Actions\PaymentRun\CreatePaymentRun;
use FluxErp\Actions\PaymentRun\DeletePaymentRun;
use FluxErp\Actions\PaymentRun\UpdatePaymentRun;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\BankConnection;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\LedgerBooking;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Settings\AccountingSettings;
use FluxErp\States\Order\PaymentState\InOpenPaymentRun;
use FluxErp\States\Order\PaymentState\InPayment;
use FluxErp\States\Order\PaymentState\Open;
use FluxErp\States\Order\PaymentState\Paid;

function createOrderForPaymentRun(
    object $testContext,
    OrderTypeEnum $orderTypeEnum = OrderTypeEnum::Order,
    ?string $grossPrice = null
): array {
    $bankConnection = BankConnection::factory()->create();
    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);
    $orderType = OrderType::factory()->create([
        'order_type_enum' => $orderTypeEnum,
        'is_active' => true,
        'is_hidden' => false,
    ]);
    $paymentType = PaymentType::factory()->hasAttached($testContext->dbTenant, relationship: 'tenants')->create();
    $order = Order::factory()->create([
        'order_type_id' => $orderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
        'tenant_id' => $testContext->dbTenant->getKey(),
        'currency_id' => Currency::factory()->create()->getKey(),
        'language_id' => $testContext->defaultLanguage->getKey(),
    ]);

    if ($grossPrice !== null) {
        $order->update(['total_gross_price' => $grossPrice, 'balance' => $grossPrice]);
    }

    return [$bankConnection, $order];
}

function createNettedPaymentRun(object $testContext): array
{
    [$bankConnection, $invoice] = createOrderForPaymentRun($testContext, OrderTypeEnum::Purchase, '-500.00');
    [, $creditNote] = createOrderForPaymentRun($testContext, OrderTypeEnum::PurchaseRefund, '500.00');

    $clearing = LedgerAccount::factory()->create(['tenant_id' => $testContext->dbTenant->getKey()]);
    $creditor = LedgerAccount::factory()->create(['tenant_id' => $testContext->dbTenant->getKey()]);

    app(AccountingSettings::class)->fill(['clearing_ledger_account_id' => $clearing->getKey()])->save();

    Contact::query()
        ->whereKey([$invoice->contact_id, $creditNote->contact_id])
        ->update(['expense_ledger_account_id' => $creditor->getKey()]);

    $run = CreatePaymentRun::make([
        'bank_connection_id' => $bankConnection->getKey(),
        'payment_run_type_enum' => 'money_transfer',
        'positions' => [
            [
                'contact_id' => $invoice->contact_id,
                'iban' => 'DE89370400440532013000',
                'orders' => [
                    ['order_id' => $invoice->getKey(), 'amount' => -500],
                    ['order_id' => $creditNote->getKey(), 'amount' => 500],
                ],
            ],
        ],
    ])->validate()->execute();

    return [$run, $invoice, $creditNote, $clearing, $creditor];
}

test('create payment run', function (): void {
    $bankConnection = BankConnection::factory()->create();
    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);
    $orderType = OrderType::factory()->create(['order_type_enum' => OrderTypeEnum::Order, 'is_active' => true]);
    $paymentType = PaymentType::factory()->hasAttached($this->dbTenant, relationship: 'tenants')->create();
    $order = Order::factory()->create([
        'order_type_id' => $orderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'currency_id' => Currency::factory()->create()->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
    ]);

    $run = CreatePaymentRun::make([
        'bank_connection_id' => $bankConnection->getKey(),
        'payment_run_type_enum' => 'money_transfer',
        'iban' => 'DE89370400440532013000',
        'orders' => [
            ['order_id' => $order->getKey(), 'amount' => -100.00],
        ],
    ])->validate()->execute();

    expect($run)->bank_connection_id->toBe($bankConnection->getKey());
});

test('create payment run requires orders and payment_run_type_enum', function (): void {
    CreatePaymentRun::assertValidationErrors([], ['orders', 'payment_run_type_enum']);
});

test('delete payment run', function (): void {
    $bankConnection = BankConnection::factory()->create();
    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);
    $orderType = OrderType::factory()->create(['order_type_enum' => OrderTypeEnum::Order, 'is_active' => true]);
    $paymentType = PaymentType::factory()->hasAttached($this->dbTenant, relationship: 'tenants')->create();
    $order = Order::factory()->create([
        'order_type_id' => $orderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'currency_id' => Currency::factory()->create()->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
    ]);

    $run = CreatePaymentRun::make([
        'bank_connection_id' => $bankConnection->getKey(),
        'payment_run_type_enum' => 'direct_debit',
        'iban' => 'DE89370400440532013000',
        'orders' => [
            ['order_id' => $order->getKey(), 'amount' => 50.00],
        ],
    ])->validate()->execute();

    expect(DeletePaymentRun::make(['id' => $run->getKey()])
        ->validate()->execute())->toBeTrue();
});

test('create payment run transitions orders to InOpenPaymentRun', function (): void {
    [$bankConnection, $order] = createOrderForPaymentRun($this);

    CreatePaymentRun::make([
        'bank_connection_id' => $bankConnection->getKey(),
        'payment_run_type_enum' => 'money_transfer',
        'iban' => 'DE89370400440532013000',
        'orders' => [
            ['order_id' => $order->getKey(), 'amount' => -100.00],
        ],
    ])->validate()->execute();

    $order->refresh();

    expect($order->payment_state)->toBeInstanceOf(InOpenPaymentRun::class);
});

test('update payment run to pending transitions orders to InPayment', function (): void {
    [$bankConnection, $order] = createOrderForPaymentRun($this);

    $run = CreatePaymentRun::make([
        'bank_connection_id' => $bankConnection->getKey(),
        'payment_run_type_enum' => 'money_transfer',
        'iban' => 'DE89370400440532013000',
        'orders' => [
            ['order_id' => $order->getKey(), 'amount' => -100.00],
        ],
    ])->validate()->execute();

    UpdatePaymentRun::make([
        'id' => $run->getKey(),
        'state' => 'pending',
    ])->validate()->execute();

    $order->refresh();

    expect($order->payment_state)->toBeInstanceOf(InPayment::class);
});

test('update payment run to successful transitions orders to InPayment', function (): void {
    [$bankConnection, $order] = createOrderForPaymentRun($this);

    $run = CreatePaymentRun::make([
        'bank_connection_id' => $bankConnection->getKey(),
        'payment_run_type_enum' => 'money_transfer',
        'iban' => 'DE89370400440532013000',
        'orders' => [
            ['order_id' => $order->getKey(), 'amount' => -100.00],
        ],
    ])->validate()->execute();

    UpdatePaymentRun::make([
        'id' => $run->getKey(),
        'state' => 'pending',
    ])->validate()->execute();

    UpdatePaymentRun::make([
        'id' => $run->getKey(),
        'state' => 'successful',
    ])->validate()->execute();

    $order->refresh();

    expect($order->payment_state)->toBeInstanceOf(InPayment::class);
});

test('update payment run to not_successful transitions orders to Open', function (): void {
    [$bankConnection, $order] = createOrderForPaymentRun($this);

    $run = CreatePaymentRun::make([
        'bank_connection_id' => $bankConnection->getKey(),
        'payment_run_type_enum' => 'money_transfer',
        'iban' => 'DE89370400440532013000',
        'orders' => [
            ['order_id' => $order->getKey(), 'amount' => -100.00],
        ],
    ])->validate()->execute();

    UpdatePaymentRun::make([
        'id' => $run->getKey(),
        'state' => 'pending',
    ])->validate()->execute();

    UpdatePaymentRun::make([
        'id' => $run->getKey(),
        'state' => 'not_successful',
    ])->validate()->execute();

    $order->refresh();

    expect($order->payment_state)->toBeInstanceOf(Open::class);
});

test('update payment run to discarded transitions orders to Open', function (): void {
    [$bankConnection, $order] = createOrderForPaymentRun($this);

    $run = CreatePaymentRun::make([
        'bank_connection_id' => $bankConnection->getKey(),
        'payment_run_type_enum' => 'money_transfer',
        'iban' => 'DE89370400440532013000',
        'orders' => [
            ['order_id' => $order->getKey(), 'amount' => -100.00],
        ],
    ])->validate()->execute();

    UpdatePaymentRun::make([
        'id' => $run->getKey(),
        'state' => 'pending',
    ])->validate()->execute();

    UpdatePaymentRun::make([
        'id' => $run->getKey(),
        'state' => 'discarded',
    ])->validate()->execute();

    $order->refresh();

    expect($order->payment_state)->toBeInstanceOf(Open::class);
});

test('delete payment run transitions orders to Open', function (): void {
    [$bankConnection, $order] = createOrderForPaymentRun($this);

    $run = CreatePaymentRun::make([
        'bank_connection_id' => $bankConnection->getKey(),
        'payment_run_type_enum' => 'money_transfer',
        'iban' => 'DE89370400440532013000',
        'orders' => [
            ['order_id' => $order->getKey(), 'amount' => -100.00],
        ],
    ])->validate()->execute();

    $order->refresh();
    expect($order->payment_state)->toBeInstanceOf(InOpenPaymentRun::class);

    DeletePaymentRun::make(['id' => $run->getKey()])
        ->validate()->execute();

    $order->refresh();

    expect($order->payment_state)->toBeInstanceOf(Open::class);
});

test('create payment run with a netted position', function (): void {
    [$bankConnection, $invoice] = createOrderForPaymentRun($this);
    [, $creditNote] = createOrderForPaymentRun($this);

    $run = CreatePaymentRun::make([
        'bank_connection_id' => $bankConnection->getKey(),
        'payment_run_type_enum' => 'money_transfer',
        'positions' => [
            [
                'contact_id' => $invoice->contact_id,
                'iban' => 'DE89370400440532013000',
                'account_holder' => 'Muster GmbH',
                'purpose' => 'RE-1, GS-1',
                'orders' => [
                    ['order_id' => $invoice->getKey(), 'amount' => -1000],
                    ['order_id' => $creditNote->getKey(), 'amount' => 200],
                ],
            ],
        ],
    ])->validate()->execute();

    $position = $run->positions()->first();

    expect($run->positions()->count())->toBe(1)
        ->and($position->amount)->toEqual('-800.00')
        ->and($position->end_to_end_id)->toBe('PR' . $run->getKey() . '-' . $position->getKey())
        ->and($position->orders()->count())->toBe(2)
        ->and($invoice->fresh()->payment_state)->toBeInstanceOf(InOpenPaymentRun::class)
        ->and($creditNote->fresh()->payment_state)->toBeInstanceOf(InOpenPaymentRun::class);
});

test('the stored purpose of a created payment run contains the position end_to_end_id once the documents overflow the limit', function (): void {
    $bankConnection = BankConnection::factory()->create();

    $orders = collect(range(1, 30))->map(function (int $i) {
        [, $order] = createOrderForPaymentRun($this, OrderTypeEnum::Purchase, '-100.00');
        $order->update(['invoice_number' => 'RE-2026-' . str_pad((string) $i, 6, '0', STR_PAD_LEFT)]);

        return $order;
    });

    $run = CreatePaymentRun::make([
        'bank_connection_id' => $bankConnection->getKey(),
        'payment_run_type_enum' => 'money_transfer',
        'positions' => [
            [
                'contact_id' => $orders->first()->contact_id,
                'iban' => 'DE89370400440532013000',
                'orders' => $orders
                    ->map(fn (Order $order) => ['order_id' => $order->getKey(), 'amount' => -100.00])
                    ->all(),
            ],
        ],
    ])->validate()->execute();

    $position = $run->positions()->sole();

    expect($position->purpose)->toContain($position->end_to_end_id)
        ->and(mb_strlen($position->purpose))->toBeLessThanOrEqual(140);
});

test('a money transfer position pointing the wrong way is rejected', function (): void {
    [$bankConnection, $order] = createOrderForPaymentRun($this);

    CreatePaymentRun::assertValidationErrors([
        'bank_connection_id' => $bankConnection->getKey(),
        'payment_run_type_enum' => 'money_transfer',
        'positions' => [
            [
                'contact_id' => $order->contact_id,
                'iban' => 'DE89370400440532013000',
                'orders' => [
                    ['order_id' => $order->getKey(), 'amount' => 100.00],
                ],
            ],
        ],
    ], ['positions.0.orders']);
});

test('create payment run still accepts the flat orders payload', function (): void {
    [$bankConnection, $order] = createOrderForPaymentRun($this);
    $order->update([
        'iban' => 'DE89370400440532013000',
        'invoice_number' => 'RE-2024-001',
    ]);

    $run = CreatePaymentRun::make([
        'bank_connection_id' => $bankConnection->getKey(),
        'payment_run_type_enum' => 'money_transfer',
        'orders' => [
            ['order_id' => $order->getKey(), 'amount' => -100.00],
        ],
    ])->validate()->execute();

    $position = $run->positions()->first();

    expect($run->positions()->count())->toBe(1)
        ->and($position->amount)->toEqual('-100.00')
        ->and($position->iban)->toBe($order->fresh()->iban)
        ->and($position->purpose)->toBe($order->fresh()->invoice_number);
});

test('delete payment run transitions InPayment orders to Open', function (): void {
    [$bankConnection, $order] = createOrderForPaymentRun($this);

    $run = CreatePaymentRun::make([
        'bank_connection_id' => $bankConnection->getKey(),
        'payment_run_type_enum' => 'money_transfer',
        'iban' => 'DE89370400440532013000',
        'orders' => [
            ['order_id' => $order->getKey(), 'amount' => -100.00],
        ],
    ])->validate()->execute();

    UpdatePaymentRun::make([
        'id' => $run->getKey(),
        'state' => 'pending',
    ])->validate()->execute();

    $order->refresh();
    expect($order->payment_state)->toBeInstanceOf(InPayment::class);

    DeletePaymentRun::make(['id' => $run->getKey()])
        ->validate()->execute();

    $order->refresh();

    expect($order->payment_state)->toBeInstanceOf(Open::class);
});

test('a position that nets to zero is settled by ledger bookings', function (): void {
    [$bankConnection, $invoice] = createOrderForPaymentRun($this);
    [, $creditNote] = createOrderForPaymentRun($this);

    $clearing = LedgerAccount::factory()->create(['tenant_id' => $this->dbTenant->getKey()]);
    $creditor = LedgerAccount::factory()->create(['tenant_id' => $this->dbTenant->getKey()]);

    app(AccountingSettings::class)->fill(['clearing_ledger_account_id' => $clearing->getKey()])->save();

    Contact::query()
        ->whereKey($invoice->contact_id)
        ->update(['expense_ledger_account_id' => $creditor->getKey()]);
    Contact::query()
        ->whereKey($creditNote->contact_id)
        ->update(['expense_ledger_account_id' => $creditor->getKey()]);

    $run = CreatePaymentRun::make([
        'bank_connection_id' => $bankConnection->getKey(),
        'payment_run_type_enum' => 'money_transfer',
        'positions' => [
            [
                'contact_id' => $invoice->contact_id,
                'iban' => 'DE89370400440532013000',
                'orders' => [
                    ['order_id' => $invoice->getKey(), 'amount' => -500],
                    ['order_id' => $creditNote->getKey(), 'amount' => 500],
                ],
            ],
        ],
    ])->validate()->execute();

    expect($run->positions()->first()->amount)->toEqual('0.00')
        ->and(LedgerBooking::query()->count())->toBe(2)
        ->and($invoice->fresh()->payment_state)->not->toBeInstanceOf(InOpenPaymentRun::class);
});

test('a position that nets to zero is rejected without a clearing account', function (): void {
    [$bankConnection, $invoice] = createOrderForPaymentRun($this);
    [, $creditNote] = createOrderForPaymentRun($this);

    app(AccountingSettings::class)->fill(['clearing_ledger_account_id' => null])->save();

    CreatePaymentRun::assertValidationErrors([
        'bank_connection_id' => $bankConnection->getKey(),
        'payment_run_type_enum' => 'money_transfer',
        'positions' => [
            [
                'contact_id' => $invoice->contact_id,
                'orders' => [
                    ['order_id' => $invoice->getKey(), 'amount' => -500],
                    ['order_id' => $creditNote->getKey(), 'amount' => 500],
                ],
            ],
        ],
    ], ['positions.0.orders']);
});

test('a position that nets to zero is rejected when one contact has no expense ledger account', function (): void {
    [$bankConnection, $invoice] = createOrderForPaymentRun($this);
    [, $creditNote] = createOrderForPaymentRun($this);

    $clearing = LedgerAccount::factory()->create(['tenant_id' => $this->dbTenant->getKey()]);
    $creditor = LedgerAccount::factory()->create(['tenant_id' => $this->dbTenant->getKey()]);

    app(AccountingSettings::class)->fill(['clearing_ledger_account_id' => $clearing->getKey()])->save();

    Contact::query()
        ->whereKey($invoice->contact_id)
        ->update(['expense_ledger_account_id' => $creditor->getKey()]);

    CreatePaymentRun::assertValidationErrors([
        'bank_connection_id' => $bankConnection->getKey(),
        'payment_run_type_enum' => 'money_transfer',
        'positions' => [
            [
                'contact_id' => $invoice->contact_id,
                'orders' => [
                    ['order_id' => $invoice->getKey(), 'amount' => -500],
                    ['order_id' => $creditNote->getKey(), 'amount' => 500],
                ],
            ],
        ],
    ], ['positions.0.orders']);

    expect(LedgerBooking::query()->count())->toBe(0);
});

test('the orders of a netted position end up paid', function (): void {
    [, $invoice, $creditNote] = createNettedPaymentRun($this);

    expect($invoice->fresh()->payment_state)->toBeInstanceOf(Paid::class)
        ->and($creditNote->fresh()->payment_state)->toBeInstanceOf(Paid::class);
});

test('the ledger bookings of a netted position hit the clearing and the expense account', function (): void {
    [, $invoice, $creditNote, $clearing, $creditor] = createNettedPaymentRun($this);

    $invoiceBooking = LedgerBooking::query()
        ->where('source_type', morph_alias(Order::class))
        ->where('source_id', $invoice->getKey())
        ->sole();
    $creditNoteBooking = LedgerBooking::query()
        ->where('source_type', morph_alias(Order::class))
        ->where('source_id', $creditNote->getKey())
        ->sole();

    expect($invoiceBooking->debit_ledger_account_id)->toBe($creditor->getKey())
        ->and($invoiceBooking->credit_ledger_account_id)->toBe($clearing->getKey())
        ->and($invoiceBooking->amount)->toEqual('500.00')
        ->and($creditNoteBooking->debit_ledger_account_id)->toBe($clearing->getKey())
        ->and($creditNoteBooking->credit_ledger_account_id)->toBe($creditor->getKey())
        ->and($creditNoteBooking->amount)->toEqual('500.00');
});

test('deleting a run leaves the orders of a netted position paid', function (): void {
    [$run, $invoice, $creditNote] = createNettedPaymentRun($this);

    DeletePaymentRun::make(['id' => $run->getKey()])
        ->validate()
        ->execute();

    expect($invoice->fresh()->payment_state)->toBeInstanceOf(Paid::class)
        ->and($creditNote->fresh()->payment_state)->toBeInstanceOf(Paid::class)
        ->and(LedgerBooking::query()->count())->toBe(2);
});

test('discarding a run leaves the orders of a netted position paid', function (): void {
    [$run, $invoice, $creditNote] = createNettedPaymentRun($this);

    UpdatePaymentRun::make([
        'id' => $run->getKey(),
        'state' => 'discarded',
    ])->validate()->execute();

    expect($invoice->fresh()->payment_state)->toBeInstanceOf(Paid::class)
        ->and($creditNote->fresh()->payment_state)->toBeInstanceOf(Paid::class);
});

test('deleting a run still opens the orders of a position that carries money', function (): void {
    [$run, $invoice, $creditNote] = createNettedPaymentRun($this);
    [, $payable] = createOrderForPaymentRun($this, OrderTypeEnum::Purchase, '-300.00');

    $position = $run->positions()->create([
        'contact_id' => $payable->contact_id,
        'iban' => 'DE02120300000000202051',
        'amount' => '-300.00',
    ]);
    $position->orders()->attach($payable->getKey(), [
        'payment_run_id' => $run->getKey(),
        'amount' => '-300.00',
    ]);
    $payable->payment_state->transitionTo(InOpenPaymentRun::class);

    DeletePaymentRun::make(['id' => $run->getKey()])
        ->validate()
        ->execute();

    expect($payable->fresh()->payment_state)->toBeInstanceOf(Open::class)
        ->and($invoice->fresh()->payment_state)->toBeInstanceOf(Paid::class)
        ->and($creditNote->fresh()->payment_state)->toBeInstanceOf(Paid::class);
});

test('an order must not appear twice in the same payment run', function (): void {
    [$bankConnection, $order] = createOrderForPaymentRun($this);

    CreatePaymentRun::assertValidationErrors([
        'bank_connection_id' => $bankConnection->getKey(),
        'payment_run_type_enum' => 'money_transfer',
        'positions' => [
            [
                'contact_id' => $order->contact_id,
                'iban' => 'DE89370400440532013000',
                'orders' => [
                    ['order_id' => $order->getKey(), 'amount' => -100],
                ],
            ],
            [
                'contact_id' => $order->contact_id,
                'iban' => 'DE02120300000000202051',
                'orders' => [
                    ['order_id' => $order->getKey(), 'amount' => -200],
                ],
            ],
        ],
    ], ['positions.1.orders.0.order_id']);
});
