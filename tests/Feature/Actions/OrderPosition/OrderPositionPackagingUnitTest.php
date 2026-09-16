<?php

use FluxErp\Actions\OrderPosition\CreateOrderPosition;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\Unit;
use FluxErp\Models\VatRate;
use FluxErp\Models\Warehouse;

beforeEach(function (): void {
    Warehouse::factory()->create(['is_default' => true]);
    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);
    $orderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Purchase,
        'is_active' => true,
    ]);
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
    $this->purchaseUnit = Unit::factory()->create(['name' => 'Box']);

    $this->product = Product::factory()->create([
        'vat_rate_id' => $this->vatRate->getKey(),
        'purchase_unit_id' => $this->purchaseUnit->getKey(),
        'purchase_steps' => 12,
    ]);
    $this->product->tenants()->attach($this->dbTenant->getKey());
});

test('a product without a purchase unit has no packaging', function (): void {
    $this->product->update(['purchase_unit_id' => null]);

    expect($this->product->packaging())->toBeNull();
});

test('a product without purchase steps has no packaging', function (): void {
    $this->product->update(['purchase_steps' => null]);

    expect($this->product->packaging())->toBeNull();
});

test('the packaging names its unit and its factor', function (): void {
    expect($this->product->packaging())
        ->toMatchArray([
            'unit_id' => $this->purchaseUnit->getKey(),
            'name' => 'Box',
        ])
        ->and(data_get($this->product->packaging(), 'factor'))->toEqual(12);
});

test('a position stores the unit it was ordered in', function (): void {
    $position = CreateOrderPosition::make([
        'order_id' => $this->order->getKey(),
        'product_id' => $this->product->getKey(),
        'vat_rate_id' => $this->vatRate->getKey(),
        'unit_id' => $this->purchaseUnit->getKey(),
        'amount' => 36,
        'unit_price' => 10,
    ])->validate()->execute();

    expect($position->amount)->toEqual(36)
        ->and($position->unit_id)->toBe($this->purchaseUnit->getKey())
        ->and($position->unit->name)->toBe('Box');
});

test('an unknown unit is rejected', function (): void {
    CreateOrderPosition::assertValidationErrors(
        [
            'order_id' => $this->order->getKey(),
            'product_id' => $this->product->getKey(),
            'vat_rate_id' => $this->vatRate->getKey(),
            'unit_id' => 99999999,
        ],
        'unit_id'
    );
});
