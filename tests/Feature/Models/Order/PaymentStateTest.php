<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Pivots\OrderTransaction;
use FluxErp\Models\PriceList;
use FluxErp\Models\Transaction;
use FluxErp\States\Order\PaymentState\Open;
use FluxErp\States\Order\PaymentState\Overpaid;
use FluxErp\States\Order\PaymentState\Paid;
use FluxErp\States\Order\PaymentState\PartialPaid;

beforeEach(function (): void {
    $contact = Contact::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create();

    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
    ]);

    $orderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
        'is_hidden' => false,
    ]);

    $this->order = Order::factory()->create([
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'currency_id' => Currency::default()->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'order_type_id' => $orderType->getKey(),
        'payment_type_id' => PaymentType::default()->getKey(),
        'price_list_id' => PriceList::default()->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    Order::query()
        ->whereKey($this->order->getKey())
        ->update([
            'invoice_number' => 'RE-1',
            'total_gross_price' => 404.12,
            'balance' => 404.12,
        ]);

    $this->assignPayment = function (float $amount): void {
        OrderTransaction::query()->create([
            'order_id' => $this->order->getKey(),
            'transaction_id' => Transaction::factory()
                ->create(['amount' => $amount, 'contact_bank_connection_id' => null])
                ->getKey(),
            'amount' => $amount,
            'is_accepted' => true,
        ]);
    };
});

test('a paid invoice is paid', function (): void {
    ($this->assignPayment)(404.12);

    expect($this->order->refresh()->payment_state)->toBeInstanceOf(Paid::class);
});

test('a partially paid invoice is partially paid', function (): void {
    ($this->assignPayment)(100);

    expect($this->order->refresh()->payment_state)->toBeInstanceOf(PartialPaid::class);
});

test('an invoice paid twice is overpaid', function (): void {
    ($this->assignPayment)(404.12);
    ($this->assignPayment)(404.12);

    $order = $this->order->refresh();

    expect($order->payment_state)->toBeInstanceOf(Overpaid::class)
        ->and($order->balance)->toEqual(-404.12);
});

test('an invoice whose payment was charged back is open again', function (): void {
    ($this->assignPayment)(404.12);
    ($this->assignPayment)(-404.12);

    $order = $this->order->refresh();

    expect($order->payment_state)->toBeInstanceOf(Open::class)
        ->and($order->balance)->toEqual(404.12);
});

test('refunding the overpayment makes the invoice paid', function (): void {
    ($this->assignPayment)(404.12);
    ($this->assignPayment)(404.12);
    ($this->assignPayment)(-404.12);

    expect($this->order->refresh()->payment_state)->toBeInstanceOf(Paid::class);
});

test('a purchase invoice paid twice is overpaid', function (): void {
    turnIntoPurchaseInvoice($this->order);

    ($this->assignPayment)(-100);
    ($this->assignPayment)(-100);

    $order = $this->order->refresh();

    expect($order->payment_state)->toBeInstanceOf(Overpaid::class)
        ->and($order->balance)->toEqual(100);
});

test('the supplier refunding an overpaid purchase invoice makes it paid', function (): void {
    turnIntoPurchaseInvoice($this->order);

    ($this->assignPayment)(-100);
    ($this->assignPayment)(-100);
    ($this->assignPayment)(100);

    $order = $this->order->refresh();

    expect($order->payment_state)->toBeInstanceOf(Paid::class)
        ->and($order->balance)->toEqual(0);
});

test('a purchase invoice whose payment was charged back is open again', function (): void {
    turnIntoPurchaseInvoice($this->order);

    ($this->assignPayment)(-100);
    ($this->assignPayment)(100);

    $order = $this->order->refresh();

    expect($order->payment_state)->toBeInstanceOf(Open::class)
        ->and($order->balance)->toEqual(-100);
});

function turnIntoPurchaseInvoice(Order $order): void
{
    Order::query()
        ->whereKey($order->getKey())
        ->update([
            'order_type_id' => OrderType::factory()
                ->create([
                    'order_type_enum' => OrderTypeEnum::Purchase,
                    'is_active' => true,
                    'is_hidden' => false,
                ])
                ->getKey(),
            'total_gross_price' => -100,
            'balance' => -100,
        ]);
}
