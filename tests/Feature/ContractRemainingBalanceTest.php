<?php

use FluxErp\Actions\Order\AdjustOrderTotal;
use FluxErp\Actions\Order\UpdateOrder;
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
use Illuminate\Validation\ValidationException;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    $this->tenant = Tenant::factory()->create();
    $this->currency = Currency::factory()->create(['is_default' => true]);
    $this->language = Language::factory()->create(['is_default' => true]);
    $this->priceList = PriceList::factory()->create(['is_default' => true]);
    $this->paymentType = PaymentType::factory()
        ->hasAttached(factory: $this->tenant, relationship: 'tenants')
        ->create(['is_default' => true]);
    $this->contact = Contact::factory()
        ->hasAttached(factory: $this->tenant, relationship: 'tenants')
        ->create();
    $this->address = Address::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'is_main_address' => true,
    ]);
    $this->vatRate = VatRate::factory()->create(['rate_percentage' => 0.19]);

    $this->purchaseSubscriptionOrderType = OrderType::factory()
        ->hasAttached(factory: $this->tenant, relationship: 'tenants')
        ->create([
            'order_type_enum' => OrderTypeEnum::PurchaseSubscription,
            'is_active' => true,
        ]);
    // The factory randomizes is_hidden; pin it so the target type stays deterministic.
    $this->purchaseOrderType = OrderType::factory()
        ->hasAttached(factory: $this->tenant, relationship: 'tenants')
        ->create([
            'order_type_enum' => OrderTypeEnum::Purchase,
            'is_active' => true,
            'is_hidden' => false,
        ]);
    $this->orderOrderType = OrderType::factory()
        ->hasAttached(factory: $this->tenant, relationship: 'tenants')
        ->create([
            'order_type_enum' => OrderTypeEnum::Order,
            'is_active' => true,
            'is_hidden' => false,
        ]);

    $this->contract = Order::factory()->create([
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
        'contract_total_amount' => 36000,
    ]);

    CreateOrderPosition::make([
        'order_id' => $this->contract->getKey(),
        'name' => 'Loan Rate',
        'vat_rate_id' => $this->vatRate->getKey(),
        'amount' => 1,
        'unit_price' => 1500.00,
        'is_net' => false,
    ])
        ->validate()
        ->execute();
});

test('contract balance counts down as rates are generated', function (): void {
    (new ProcessSubscriptionOrder())($this->contract->getKey(), $this->purchaseOrderType->getKey());

    expect(bcround($this->contract->fresh()->balance, 2))->toBe('34500.00');
});

test('adjusting a rate refreshes the contract balance', function (): void {
    (new ProcessSubscriptionOrder())($this->contract->getKey(), $this->purchaseOrderType->getKey());
    $child = $this->contract->createdOrders()->latest('id')->first();

    AdjustOrderTotal::make([
        'id' => $child->getKey(),
        'total_gross_price' => 1512.38,
    ])
        ->validate()
        ->execute();

    expect(bcround($this->contract->fresh()->balance, 2))->toBe('34487.62');
});

test('changing the contract total recalculates the balance', function (): void {
    (new ProcessSubscriptionOrder())($this->contract->getKey(), $this->purchaseOrderType->getKey());

    UpdateOrder::make([
        'id' => $this->contract->getKey(),
        'contract_total_amount' => 37000,
    ])
        ->validate()
        ->execute();

    expect(bcround($this->contract->fresh()->balance, 2))->toBe('35500.00');
});

test('a contract without a total keeps its balance untouched', function (): void {
    $this->contract->update(['contract_total_amount' => null, 'balance' => null]);

    (new ProcessSubscriptionOrder())($this->contract->getKey(), $this->purchaseOrderType->getKey());

    expect($this->contract->fresh()->balance)->toBeNull();
});

test('a contract balance never appears in the unpaid scope', function (): void {
    (new ProcessSubscriptionOrder())($this->contract->getKey(), $this->purchaseOrderType->getKey());

    expect(Order::query()->unpaid()->whereKey($this->contract->getKey())->exists())->toBeFalse();
});

test('contract total amount is rejected on non subscription orders', function (): void {
    $order = Order::factory()->create([
        'tenant_id' => $this->tenant->getKey(),
        'contact_id' => $this->contact->getKey(),
        'address_invoice_id' => $this->address->getKey(),
        'order_type_id' => $this->orderOrderType->getKey(),
        'currency_id' => $this->currency->getKey(),
        'language_id' => $this->language->getKey(),
        'price_list_id' => $this->priceList->getKey(),
        'payment_type_id' => $this->paymentType->getKey(),
        'is_locked' => false,
    ]);

    try {
        UpdateOrder::make([
            'id' => $order->getKey(),
            'contract_total_amount' => 1000,
        ])
            ->validate()
            ->execute();

        $this->fail('Expected contract_total_amount to be rejected.');
    } catch (ValidationException $e) {
        expect($e->errors())->toHaveKey('contract_total_amount');
    }
});
