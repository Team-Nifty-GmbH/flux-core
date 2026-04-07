<?php

use FluxErp\Actions\SerialNumberRange\CreateSerialNumberRange;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;

beforeEach(function (): void {
    // Use Order with order_number — less likely to conflict with global setup
    CreateSerialNumberRange::make([
        'tenant_id' => $this->dbTenant->getKey(),
        'model_type' => morph_alias(Order::class),
        'type' => 'order_number',
        'start_number' => 5000,
        'prefix' => 'ORD-',
    ])->validate()->execute();

    $contact = Contact::factory()->create();
    $this->address = Address::factory()->create(['contact_id' => $contact->getKey()]);
    $this->orderType = OrderType::factory()->create(['order_type_enum' => OrderTypeEnum::Order, 'is_active' => true]);
    $this->paymentType = PaymentType::factory()->hasAttached($this->dbTenant, relationship: 'tenants')->create();
    $this->priceList = PriceList::factory()->create();
    $this->currency = Currency::factory()->create();
    $this->contact = $contact;
});

test('getSerialNumber generates number with prefix', function (): void {
    $order = Order::factory()->create([
        'order_type_id' => $this->orderType->getKey(),
        'address_invoice_id' => $this->address->getKey(),
        'contact_id' => $this->contact->getKey(),
        'payment_type_id' => $this->paymentType->getKey(),
        'price_list_id' => $this->priceList->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'currency_id' => $this->currency->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
    ]);

    $order->getSerialNumber('order_number');

    expect($order->fresh()->order_number)->toStartWith('ORD-');
});
