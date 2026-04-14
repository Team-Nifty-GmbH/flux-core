<?php

use FluxErp\Actions\OrderPosition\CreateOrderPosition;
use FluxErp\Actions\OrderPosition\DeleteOrderPosition;
use FluxErp\Actions\OrderPosition\UpdateOrderPosition;
use FluxErp\Enums\BundleTypeEnum;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
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

test('group bundle product is not marked as free text when origin_position_id is set', function (): void {
    $product = Product::factory()->create([
        'bundle_type_enum' => BundleTypeEnum::Group,
    ]);

    $retoureType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Retoure,
        'is_active' => true,
    ]);

    $parentOrder = $this->order;
    $parentPosition = OrderPosition::factory()->create([
        'order_id' => $parentOrder->getKey(),
        'vat_rate_id' => $this->vatRate->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'amount' => 5,
        'signed_amount' => 5,
    ]);

    $childOrder = Order::factory()->create([
        'order_type_id' => $retoureType->getKey(),
        'parent_id' => $parentOrder->getKey(),
        'address_invoice_id' => $parentOrder->address_invoice_id,
        'contact_id' => $parentOrder->contact_id,
        'payment_type_id' => $parentOrder->payment_type_id,
        'price_list_id' => $parentOrder->price_list_id,
        'tenant_id' => $this->dbTenant->getKey(),
        'currency_id' => $parentOrder->currency_id,
        'language_id' => $parentOrder->language_id,
        'is_locked' => false,
    ]);

    $position = CreateOrderPosition::make([
        'order_id' => $childOrder->getKey(),
        'product_id' => $product->getKey(),
        'origin_position_id' => $parentPosition->getKey(),
        'vat_rate_id' => $this->vatRate->getKey(),
        'amount' => 1,
    ])
        ->validate()
        ->execute();

    expect($position->is_free_text)->toBeFalse();
});

test('origin position validation excludes soft deleted positions', function (): void {
    $retoureType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Retoure,
        'is_active' => true,
    ]);

    $parentOrder = $this->order;
    $deletedPosition = OrderPosition::factory()->create([
        'order_id' => $parentOrder->getKey(),
        'vat_rate_id' => $this->vatRate->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'amount' => 5,
        'deleted_at' => now(),
    ]);

    $childOrder = Order::factory()->create([
        'order_type_id' => $retoureType->getKey(),
        'parent_id' => $parentOrder->getKey(),
        'address_invoice_id' => $parentOrder->address_invoice_id,
        'contact_id' => $parentOrder->contact_id,
        'payment_type_id' => $parentOrder->payment_type_id,
        'price_list_id' => $parentOrder->price_list_id,
        'tenant_id' => $this->dbTenant->getKey(),
        'currency_id' => $parentOrder->currency_id,
        'language_id' => $parentOrder->language_id,
        'is_locked' => false,
    ]);

    CreateOrderPosition::assertValidationErrors([
        'order_id' => $childOrder->getKey(),
        'origin_position_id' => $deletedPosition->getKey(),
        'vat_rate_id' => $this->vatRate->getKey(),
        'amount' => 1,
    ], 'origin_position_id');
});

test('amount validation only runs when origin position exists in parent order', function (): void {
    $retoureType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Retoure,
        'is_active' => true,
    ]);

    $parentOrder = $this->order;
    $childOrder = Order::factory()->create([
        'order_type_id' => $retoureType->getKey(),
        'parent_id' => $parentOrder->getKey(),
        'address_invoice_id' => $parentOrder->address_invoice_id,
        'contact_id' => $parentOrder->contact_id,
        'payment_type_id' => $parentOrder->payment_type_id,
        'price_list_id' => $parentOrder->price_list_id,
        'tenant_id' => $this->dbTenant->getKey(),
        'currency_id' => $parentOrder->currency_id,
        'language_id' => $parentOrder->language_id,
        'is_locked' => false,
    ]);

    // Missing origin_position_id should fail on origin_position_id, not on amount
    CreateOrderPosition::assertValidationErrors([
        'order_id' => $childOrder->getKey(),
        'name' => 'Test Position',
        'vat_rate_id' => $this->vatRate->getKey(),
        'unit_price' => 10,
        'amount' => 999,
    ], 'origin_position_id');
});
