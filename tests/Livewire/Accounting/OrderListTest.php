<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Accounting\OrderList;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Tenant;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use function Livewire\invade;

uses(DatabaseTransactions::class);

test('renders successfully', function (): void {
    Livewire::test(OrderList::class)
        ->assertOk();
});

test('get builder excludes subscription order types', function (): void {
    $contact = Contact::factory()
        ->hasAttached(factory: Tenant::default(), relationship: 'tenants')
        ->create();
    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
    ]);

    $subscriptionOrderType = OrderType::factory()
        ->hasAttached(factory: Tenant::default(), relationship: 'tenants')
        ->create(['order_type_enum' => OrderTypeEnum::Subscription, 'is_active' => true]);
    $purchaseSubscriptionOrderType = OrderType::factory()
        ->hasAttached(factory: Tenant::default(), relationship: 'tenants')
        ->create(['order_type_enum' => OrderTypeEnum::PurchaseSubscription, 'is_active' => true]);
    $regularOrderType = OrderType::factory()
        ->hasAttached(factory: Tenant::default(), relationship: 'tenants')
        ->create(['order_type_enum' => OrderTypeEnum::Order, 'is_active' => true]);

    $orderAttributes = [
        'tenant_id' => Tenant::default()->getKey(),
        'contact_id' => $contact->getKey(),
        'address_invoice_id' => $address->getKey(),
        'currency_id' => Currency::default()->getKey(),
        'language_id' => Language::default()->getKey(),
        'price_list_id' => PriceList::default()->getKey(),
        'payment_type_id' => PaymentType::default()->getKey(),
        'invoice_number' => 'INV-1',
    ];

    $subscriptionOrder = Order::factory()->create([...$orderAttributes, 'order_type_id' => $subscriptionOrderType->getKey(), 'invoice_number' => 'INV-SUB'])->fresh();
    $purchaseSubscriptionOrder = Order::factory()->create([...$orderAttributes, 'order_type_id' => $purchaseSubscriptionOrderType->getKey(), 'invoice_number' => 'INV-PURCHASE-SUB'])->fresh();
    $regularOrder = Order::factory()->create([...$orderAttributes, 'order_type_id' => $regularOrderType->getKey(), 'invoice_number' => 'INV-REGULAR'])->fresh();

    $component = Livewire::test(OrderList::class);
    $orderIds = invade($component->instance())->getBuilder(Order::query())->pluck('id');

    expect($orderIds)->toContain($regularOrder->getKey())
        ->and($orderIds)->not->toContain($subscriptionOrder->getKey())
        ->and($orderIds)->not->toContain($purchaseSubscriptionOrder->getKey());
});
