<?php

use FluxErp\Actions\OrderPosition\CreateOrderPosition;
use FluxErp\Actions\OrderPosition\DeleteOrderPosition;
use FluxErp\Actions\OrderPosition\UpdateOrderPosition;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\VatRate;
use FluxErp\Models\Warehouse;

beforeEach(function (): void {
    Warehouse::factory()->create(['is_default' => true]);
    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);
    $orderType = OrderType::factory()->create(['order_type_enum' => OrderTypeEnum::Order, 'is_active' => true]);
    $paymentType = PaymentType::factory()->hasAttached($this->dbTenant, relationship: 'tenants')->create();

    $this->order = Order::factory()->create([
        'order_type_id' => $orderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'currency_id' => Currency::factory()->create()->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'is_locked' => false,
    ]);
    $this->vatRate = VatRate::factory()->create();
});

test('create order position with name', function (): void {
    $position = CreateOrderPosition::make([
        'order_id' => $this->order->getKey(),
        'name' => 'Custom Position',
        'vat_rate_id' => $this->vatRate->getKey(),
        'amount' => 1,
        'unit_price' => 50.00,
    ])->validate()->execute();

    expect($position)
        ->toBeInstanceOf(OrderPosition::class)
        ->name->toBe('Custom Position');
});

test('create order position requires order_id', function (): void {
    CreateOrderPosition::assertValidationErrors(
        ['name' => 'Test'],
        'order_id'
    );
});

test('update order position', function (): void {
    $position = OrderPosition::factory()->create([
        'order_id' => $this->order->getKey(),
        'vat_rate_id' => $this->vatRate->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    $updated = UpdateOrderPosition::make([
        'id' => $position->getKey(),
        'name' => 'Updated Position',
    ])->validate()->execute();

    expect($updated->name)->toBe('Updated Position');
});

test('delete order position', function (): void {
    $position = OrderPosition::factory()->create([
        'order_id' => $this->order->getKey(),
        'vat_rate_id' => $this->vatRate->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    $result = DeleteOrderPosition::make(['id' => $position->getKey()])
        ->validate()->execute();

    expect($result)->toBeTrue();
});
