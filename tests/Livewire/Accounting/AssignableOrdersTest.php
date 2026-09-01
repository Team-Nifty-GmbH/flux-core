<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Accounting\AssignableOrders;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->contact = Contact::factory()->create();
    $address = Address::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'is_main_address' => true,
        'is_invoice_address' => true,
    ]);
    $currency = Currency::factory()->create(['is_default' => true]);
    $paymentType = PaymentType::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create();

    $purchaseType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Purchase,
        'is_active' => true,
    ]);
    $subscriptionType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::PurchaseSubscription,
        'is_active' => true,
    ]);

    $orderData = [
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $this->contact->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'currency_id' => $currency->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'invoice_number' => null,
        'is_locked' => false,
    ];

    $this->contract = Order::factory()->create(array_merge($orderData, [
        'order_type_id' => $subscriptionType->getKey(),
        'total_gross_price' => -2400,
    ]));
    $this->rate = Order::factory()->create(array_merge($orderData, [
        'order_type_id' => $purchaseType->getKey(),
        'created_from_id' => $this->contract->getKey(),
        'total_gross_price' => -200,
    ]));
    $this->purchaseOrder = Order::factory()->create(array_merge($orderData, [
        'order_type_id' => $purchaseType->getKey(),
        'total_gross_price' => -890,
    ]));
    $this->invoiced = Order::factory()->create(array_merge($orderData, [
        'order_type_id' => $purchaseType->getKey(),
        'invoice_number' => 'RE-1',
        'total_gross_price' => -100,
    ]));
});

test('renders successfully', function (): void {
    Livewire::test(AssignableOrders::class)
        ->assertOk()
        ->assertCount('orders', 0);
});

test('the load event groups the open orders of the contact', function (): void {
    $component = Livewire::test(AssignableOrders::class)
        ->dispatch('assignable-orders.load', contactId: $this->contact->getKey(), invoiceTotal: 200)
        ->assertOk()
        ->assertSet('contactId', $this->contact->getKey())
        ->assertSet('invoiceTotal', 200.0);

    $groups = collect($component->get('orders'))->mapWithKeys(fn (array $group) => [
        $group['label'] => collect($group['value'])->pluck('value')->all(),
    ]);

    expect($groups->get(__('Subscription Rates')))->toBe([$this->rate->getKey()])
        ->and($groups->get(__('Orders')))->toContain($this->purchaseOrder->getKey())
        ->and($groups->get(__('Orders')))->toContain($this->contract->getKey())
        ->and($groups->get(__('Orders')))->not->toContain($this->invoiced->getKey());
});

test('choosing an order tells the parent and flags a deviating amount', function (): void {
    Livewire::test(AssignableOrders::class)
        ->dispatch('assignable-orders.load', contactId: $this->contact->getKey(), invoiceTotal: 200)
        ->set('orderId', $this->rate->getKey())
        ->assertSet('hasDeviation', false)
        ->assertDispatched('assignable-orders.selected')
        ->set('orderId', $this->purchaseOrder->getKey())
        ->assertSet('hasDeviation', true);
});

test('a new load event clears the previous choice', function (): void {
    Livewire::test(AssignableOrders::class)
        ->dispatch('assignable-orders.load', contactId: $this->contact->getKey(), invoiceTotal: 200)
        ->set('orderId', $this->purchaseOrder->getKey())
        ->assertSet('hasDeviation', true)
        ->dispatch('assignable-orders.load', contactId: null, invoiceTotal: null)
        ->assertSet('orderId', null)
        ->assertSet('hasDeviation', false)
        ->assertCount('orders', 0);
});

test('the single subscription rate matching the invoice total is preselected', function (): void {
    Livewire::test(AssignableOrders::class)
        ->dispatch('assignable-orders.load', contactId: $this->contact->getKey(), invoiceTotal: 200)
        ->assertOk()
        ->assertSet('orderId', $this->rate->getKey())
        ->assertSet('hasDeviation', false)
        ->assertDispatched('assignable-orders.selected');
});

test('nothing is preselected when several rates carry the invoice total', function (): void {
    Order::factory()->create([
        'order_type_id' => $this->rate->order_type_id,
        'address_invoice_id' => $this->rate->address_invoice_id,
        'contact_id' => $this->contact->getKey(),
        'payment_type_id' => $this->rate->payment_type_id,
        'price_list_id' => $this->rate->price_list_id,
        'tenant_id' => $this->dbTenant->getKey(),
        'currency_id' => $this->rate->currency_id,
        'language_id' => $this->defaultLanguage->getKey(),
        'created_from_id' => $this->contract->getKey(),
        'invoice_number' => null,
        'is_locked' => false,
        'total_gross_price' => -200,
    ]);

    Livewire::test(AssignableOrders::class)
        ->dispatch('assignable-orders.load', contactId: $this->contact->getKey(), invoiceTotal: 200)
        ->assertSet('orderId', null)
        ->assertNotDispatched('assignable-orders.selected');
});

test('a plain purchase order matching the total is never preselected', function (): void {
    Livewire::test(AssignableOrders::class)
        ->dispatch('assignable-orders.load', contactId: $this->contact->getKey(), invoiceTotal: 890)
        ->assertSet('orderId', null)
        ->assertNotDispatched('assignable-orders.selected');
});

test('nothing is preselected without an invoice total', function (): void {
    Livewire::test(AssignableOrders::class)
        ->dispatch('assignable-orders.load', contactId: $this->contact->getKey())
        ->assertSet('orderId', null);
});
