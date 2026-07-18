<?php

use FluxErp\Actions\Order\AdjustOrderTotal;
use FluxErp\Actions\Order\ReplicateOrder;
use FluxErp\Actions\OrderPosition\CreateOrderPosition;
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
use FluxErp\Models\VatRate;
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

    $this->vatRate = VatRate::factory()->create([
        'rate_percentage' => 0.19,
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
        'is_locked' => false,
        'shipping_costs_net_price' => 0,
    ]);

    CreateOrderPosition::make([
        'order_id' => $this->order->getKey(),
        'name' => 'Subscription Fee',
        'vat_rate_id' => $this->vatRate->getKey(),
        'amount' => 1,
        'unit_price' => 1500.00,
        'is_net' => false,
    ])
        ->validate()
        ->execute();
});

test('adjust order total sets the gross total to the payment amount', function (): void {
    (new ProcessSubscriptionOrder())($this->order->getKey(), $this->targetOrderType->getKey());

    $child = $this->order->createdOrders()->latest('id')->first();

    AdjustOrderTotal::make([
        'id' => $child->getKey(),
        'total_gross_price' => 1512.38,
    ])
        ->validate()
        ->execute();

    $child->refresh();
    expect(bcround($child->total_gross_price, 2))->toBe('1512.38');
    expect(bcround($child->balance, 2))->toBe('1512.38');
});

test('adjust order total rejects orders that are not subscription children', function (): void {
    AdjustOrderTotal::make([
        'id' => $this->order->getKey(),
        'total_gross_price' => 100,
    ])
        ->validate()
        ->execute();
})->throws(Illuminate\Validation\ValidationException::class);

test('adjust order total rejects children of non-subscription parents', function (): void {
    $orderOrderType = OrderType::factory()
        ->hasAttached(factory: $this->tenant, relationship: 'tenants')
        ->create([
            'order_type_enum' => OrderTypeEnum::Order,
            'is_active' => true,
        ]);

    $splitOrderType = OrderType::factory()
        ->hasAttached(factory: $this->tenant, relationship: 'tenants')
        ->create([
            'order_type_enum' => OrderTypeEnum::SplitOrder,
            'is_active' => true,
        ]);

    $parent = Order::factory()->create([
        'tenant_id' => $this->tenant->getKey(),
        'contact_id' => $this->contact->getKey(),
        'address_invoice_id' => $this->address->getKey(),
        'order_type_id' => $orderOrderType->getKey(),
        'currency_id' => $this->currency->getKey(),
        'language_id' => $this->language->getKey(),
        'price_list_id' => $this->priceList->getKey(),
        'payment_type_id' => $this->paymentType->getKey(),
        'parent_id' => null,
        'is_locked' => false,
    ]);

    CreateOrderPosition::make([
        'order_id' => $parent->getKey(),
        'name' => 'Product',
        'vat_rate_id' => $this->vatRate->getKey(),
        'amount' => 1,
        'unit_price' => 100.00,
        'is_net' => false,
    ])
        ->validate()
        ->execute();

    $child = ReplicateOrder::make([
        'id' => $parent->getKey(),
        'address_invoice_id' => $this->address->getKey(),
        'order_type_id' => $splitOrderType->getKey(),
    ])
        ->validate()
        ->execute();

    AdjustOrderTotal::make([
        'id' => $child->getKey(),
        'total_gross_price' => 50,
    ])
        ->validate()
        ->execute();
})->throws(Illuminate\Validation\ValidationException::class);

test('adjust order total rejects locked orders', function (): void {
    (new ProcessSubscriptionOrder())($this->order->getKey(), $this->targetOrderType->getKey());

    $child = $this->order->createdOrders()->latest('id')->first();
    $child->update(['is_locked' => true]);

    AdjustOrderTotal::make([
        'id' => $child->getKey(),
        'total_gross_price' => 100,
    ])
        ->validate()
        ->execute();
})->throws(Illuminate\Validation\ValidationException::class);

test('adjust order total rejects orders with more than one position', function (): void {
    (new ProcessSubscriptionOrder())($this->order->getKey(), $this->targetOrderType->getKey());

    $child = $this->order->createdOrders()->latest('id')->first();

    CreateOrderPosition::make([
        'order_id' => $child->getKey(),
        'name' => 'Extra Position',
        'vat_rate_id' => $this->vatRate->getKey(),
        'amount' => 1,
        'unit_price' => 10.00,
        'is_net' => false,
    ])
        ->validate()
        ->execute();

    AdjustOrderTotal::make([
        'id' => $child->getKey(),
        'total_gross_price' => 100,
    ])
        ->validate()
        ->execute();
})->throws(Illuminate\Validation\ValidationException::class);
