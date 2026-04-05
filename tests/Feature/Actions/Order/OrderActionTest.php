<?php

use FluxErp\Actions\Order\CreateOrder;
use FluxErp\Actions\Order\DeleteOrder;
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

test('create order', function (): void {
    $order = CreateOrder::make([
        'contact_id' => $this->contact->getKey(),
        'address_invoice_id' => $this->address->getKey(),
        'order_type_id' => $this->orderType->getKey(),
        'payment_type_id' => $this->paymentType->getKey(),
        'price_list_id' => $this->priceList->getKey(),
        'currency_id' => $this->currency->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
    ])->validate()->execute();

    expect($order)
        ->toBeInstanceOf(Order::class)
        ->order_number->not->toBeNull()
        ->contact_id->toBe($this->contact->getKey());
});

test('create order requires contact and address', function (): void {
    CreateOrder::assertValidationErrors([
        'order_type_id' => $this->orderType->getKey(),
    ], ['contact_id', 'address_invoice_id']);
});

test('update order', function (): void {
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

    $updated = UpdateOrder::make([
        'id' => $order->getKey(),
        'header' => 'Updated header text',
    ])->validate()->execute();

    expect($updated->header)->toBe('Updated header text');
});

test('update locked order fails', function (): void {
    $order = Order::factory()->create([
        'order_type_id' => $this->orderType->getKey(),
        'address_invoice_id' => $this->address->getKey(),
        'contact_id' => $this->contact->getKey(),
        'payment_type_id' => $this->paymentType->getKey(),
        'price_list_id' => $this->priceList->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'currency_id' => $this->currency->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'is_locked' => true,
    ]);

    UpdateOrder::assertValidationErrors([
        'id' => $order->getKey(),
        'header' => 'Should fail',
    ], 'is_locked');
});

test('delete order', function (): void {
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

    $result = DeleteOrder::make(['id' => $order->getKey()])
        ->validate()->execute();

    expect($result)->toBeTrue();
});

test('delete locked order fails', function (): void {
    $order = Order::factory()->create([
        'order_type_id' => $this->orderType->getKey(),
        'address_invoice_id' => $this->address->getKey(),
        'contact_id' => $this->contact->getKey(),
        'payment_type_id' => $this->paymentType->getKey(),
        'price_list_id' => $this->priceList->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'currency_id' => $this->currency->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'is_locked' => true,
    ]);

    DeleteOrder::assertValidationErrors([
        'id' => $order->getKey(),
    ], 'is_locked');
});
