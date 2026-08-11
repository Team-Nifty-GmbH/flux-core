<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Accounting\OutstandingItems;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use Livewire\Livewire;

function outstandingIds(): array
{
    $component = new OutstandingItems();
    $method = new ReflectionMethod(OutstandingItems::class, 'getBuilder');
    $method->setAccessible(true);

    return $method->invoke($component, Order::query())->pluck('id')->all();
}

function createOutstandingOrder(object $test, array $orderAttributes = [], array $rawAttributes = []): Order
{
    $contact = Contact::factory()->create();
    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
        'is_invoice_address' => true,
    ]);

    $orderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
        'is_hidden' => false,
    ]);

    $paymentType = PaymentType::factory()
        ->hasAttached($test->dbTenant, relationship: 'tenants')
        ->create(['is_direct_debit' => false]);

    $order = Order::factory()->create(array_merge([
        'order_type_id' => $orderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
        'tenant_id' => $test->dbTenant->getKey(),
        'currency_id' => Currency::factory()->create()->getKey(),
        'language_id' => $test->defaultLanguage->getKey(),
        'is_locked' => true,
        'invoice_number' => 'INV-' . fake()->unique()->numberBetween(1000, 9999),
    ], $orderAttributes));

    Order::query()->whereKey($order->getKey())->update(array_merge([
        'balance' => 250,
        'payment_state' => 'open',
    ], $rawAttributes));

    return $order->refresh();
}

test('outstanding items renders', function (): void {
    Livewire::test(OutstandingItems::class)
        ->assertOk();
});

test('outstanding items lists an unpaid invoice', function (): void {
    $order = createOutstandingOrder($this);

    expect(outstandingIds())->toContain($order->getKey());
});

test('outstanding items hides orders without an invoice number', function (): void {
    $order = createOutstandingOrder($this, ['invoice_number' => null]);

    expect(outstandingIds())->not->toContain($order->getKey());
});

test('outstanding items hides settled invoices', function (): void {
    $order = createOutstandingOrder($this, [], ['balance' => 0, 'payment_state' => 'paid']);

    expect(outstandingIds())->not->toContain($order->getKey());
});

test('outstanding items hides purchase orders', function (): void {
    $purchaseOrderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Purchase,
        'is_active' => true,
        'is_hidden' => false,
    ]);

    $order = createOutstandingOrder($this, ['order_type_id' => $purchaseOrderType->getKey()]);

    expect(outstandingIds())->not->toContain($order->getKey());
});

test('outstanding items lists an invoice that is not due for a reminder yet', function (): void {
    $order = createOutstandingOrder($this, [], [
        'payment_reminder_next_date' => now()->addDays(30)->toDateString(),
    ]);

    expect(outstandingIds())->toContain($order->getKey());
});
