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

function createOrderForPaymentRun(object $testContext): array
{
    $bankConnection = BankConnection::factory()->create();
    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);
    $orderType = OrderType::factory()->create(['order_type_enum' => OrderTypeEnum::Order, 'is_active' => true]);
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

    return [$bankConnection, $order];
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
            ['order_id' => $order->getKey(), 'amount' => 100.00],
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
            ['order_id' => $order->getKey(), 'amount' => 100.00],
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
            ['order_id' => $order->getKey(), 'amount' => 100.00],
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
            ['order_id' => $order->getKey(), 'amount' => 100.00],
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
            ['order_id' => $order->getKey(), 'amount' => 100.00],
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
            ['order_id' => $order->getKey(), 'amount' => 100.00],
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
            ['order_id' => $order->getKey(), 'amount' => 100.00],
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
            ['order_id' => $order->getKey(), 'amount' => 100.00],
        ],
    ])->validate()->execute();

    $position = $run->positions()->first();

    expect($run->positions()->count())->toBe(1)
        ->and($position->amount)->toEqual('100.00')
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
            ['order_id' => $order->getKey(), 'amount' => 100.00],
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
