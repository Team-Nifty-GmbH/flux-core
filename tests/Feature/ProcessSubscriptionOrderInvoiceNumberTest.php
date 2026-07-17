<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Invokable\ProcessSubscriptionOrder;
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

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    $this->tenant = Tenant::factory()->create();

    $this->currency = Currency::factory()->create([
        'is_default' => true,
    ]);

    $this->language = Language::factory()->create([
        'is_default' => true,
    ]);

    $this->priceList = PriceList::factory()->create([
        'is_default' => true,
    ]);

    $this->paymentType = PaymentType::factory()
        ->hasAttached(factory: $this->tenant, relationship: 'tenants')
        ->create([
            'is_default' => true,
        ]);

    $this->contact = Contact::factory()
        ->hasAttached(factory: $this->tenant, relationship: 'tenants')
        ->create();

    $this->address = Address::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'is_main_address' => true,
    ]);

    $this->purchaseSubscriptionOrderType = OrderType::factory()
        ->hasAttached(factory: $this->tenant, relationship: 'tenants')
        ->create([
            'order_type_enum' => OrderTypeEnum::PurchaseSubscription,
            'is_active' => true,
        ]);

    $this->targetOrderType = OrderType::factory()
        ->hasAttached(factory: $this->tenant, relationship: 'tenants')
        ->create([
            'order_type_enum' => OrderTypeEnum::Purchase,
            'is_active' => true,
        ]);

    $this->order = Order::factory()->create([
        'tenant_id' => $this->tenant->getKey(),
        'contact_id' => $this->contact->getKey(),
        'address_invoice_id' => $this->address->getKey(),
        'order_type_id' => $this->purchaseSubscriptionOrderType->getKey(),
        'currency_id' => $this->currency->getKey(),
        'language_id' => $this->language->getKey(),
        'price_list_id' => $this->priceList->getKey(),
        'payment_type_id' => $this->paymentType->getKey(),
        'parent_id' => null,
        'order_number' => 'K-100',
        'system_delivery_date' => '2026-07-01',
        'system_delivery_date_end' => null,
    ]);
});

test('replicated purchase subscription order gets a generated invoice number', function (): void {
    (new ProcessSubscriptionOrder())($this->order->getKey(), $this->targetOrderType->getKey());

    $child = $this->order->createdOrders()->latest('id')->first();

    expect($child->invoice_number)->toBe($this->order->order_number . '-2026-07')
        ->and($child->invoice_date->toDateString())->toBe($child->system_delivery_date->toDateString());
});

test('child order resolves contract invoice media as fallback', function (): void {
    $this->order->addMediaFromString('%PDF-1.4 dummy')
        ->usingFileName('contract.pdf')
        ->toMediaCollection('invoice');

    (new ProcessSubscriptionOrder())($this->order->getKey(), $this->targetOrderType->getKey());

    $child = $this->order->createdOrders()->latest('id')->first();

    expect($child->invoice())->not->toBeNull();
});
