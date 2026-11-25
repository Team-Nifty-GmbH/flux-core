<?php

use FluxErp\Actions\Order\UpdateOrder;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Warehouse;

beforeEach(function (): void {
    Warehouse::factory()->create(['is_default' => true]);
    $this->contact = Contact::factory()->create();
    $this->address = Address::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'is_main_address' => true,
        'is_invoice_address' => true,
    ]);
    $this->orderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
    ]);
    $this->paymentType = PaymentType::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create();
    $this->priceList = PriceList::factory()->create();
    $this->currency = Currency::factory()->create();
});

test('payment purpose pattern can be set on an order', function (): void {
    $order = Order::factory()->create([
        'order_type_id' => $this->orderType->getKey(),
        'address_invoice_id' => $this->address->getKey(),
        'contact_id' => $this->contact->getKey(),
        'payment_type_id' => $this->paymentType->getKey(),
        'price_list_id' => $this->priceList->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'currency_id' => $this->currency->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'is_locked' => false,
    ]);

    UpdateOrder::make([
        'id' => $order->getKey(),
        'payment_purpose_pattern' => 'Miete Musterstr. 1',
    ])
        ->validate()
        ->execute();

    expect($order->refresh()->payment_purpose_pattern)->toBe('Miete Musterstr. 1');
});
