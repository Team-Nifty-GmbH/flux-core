<?php

use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentRun;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\States\Order\PaymentState\InOpenPaymentRun;
use FluxErp\States\Order\PaymentState\Open;
use FluxErp\States\Order\PaymentState\Paid;

function createOrderForPaymentRun(object $test, string $paymentState = InOpenPaymentRun::class): Order
{
    $contact = Contact::factory()->create();
    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
        'is_invoice_address' => true,
    ]);

    $order = Order::factory()->create([
        'order_type_id' => OrderType::factory()->create(['is_active' => true])->getKey(),
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'payment_type_id' => PaymentType::factory()
            ->hasAttached($test->dbTenant, relationship: 'tenants')
            ->create()
            ->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
        'currency_id' => Currency::factory()->create()->getKey(),
        'language_id' => $test->defaultLanguage->getKey(),
        'tenant_id' => $test->dbTenant->getKey(),
    ]);

    Order::query()->whereKey($order->getKey())->update(['payment_state' => $paymentState::$name]);

    return $order->refresh();
}

test('removing an order from a payment run reopens it', function (): void {
    $paymentRun = PaymentRun::query()->create([
        'payment_run_type_enum' => 'money_transfer',
        'state' => 'open',
    ]);

    $order = createOrderForPaymentRun($this);
    $paymentRun->orders()->attach($order->getKey(), ['amount' => '100.00']);

    $paymentRun->orders()->detach($order->getKey());

    expect($order->refresh()->payment_state)->toBeInstanceOf(Open::class);
});

test('removing a settled order from a payment run keeps it paid', function (): void {
    $paymentRun = PaymentRun::query()->create([
        'payment_run_type_enum' => 'money_transfer',
        'state' => 'open',
    ]);

    $order = createOrderForPaymentRun($this, Paid::class);
    $paymentRun->orders()->attach($order->getKey(), ['amount' => '100.00']);

    $paymentRun->orders()->detach($order->getKey());

    expect($order->refresh()->payment_state)->toBeInstanceOf(Paid::class);
});

test('an order that sits in another open run stays in a payment run state', function (): void {
    $first = PaymentRun::query()->create([
        'payment_run_type_enum' => 'money_transfer',
        'state' => 'open',
    ]);
    $second = PaymentRun::query()->create([
        'payment_run_type_enum' => 'money_transfer',
        'state' => 'open',
    ]);

    $order = createOrderForPaymentRun($this);
    $first->orders()->attach($order->getKey(), ['amount' => '100.00']);
    $second->orders()->attach($order->getKey(), ['amount' => '100.00']);

    $first->orders()->detach($order->getKey());

    expect($order->refresh()->payment_state)->toBeInstanceOf(InOpenPaymentRun::class);
});
