<?php

use FluxErp\Actions\WorkTime\CreateOrdersFromWorkTimes;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Enums\TimeUnitEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\WorkTime;

beforeEach(function (): void {
    $this->contact = Contact::factory()->create();

    Address::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'is_main_address' => true,
    ]);

    $language = Language::factory()->create();
    $currency = Currency::factory()->create(['is_default' => true]);
    PriceList::factory()->create();

    $this->orderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
    ]);

    $this->paymentType = PaymentType::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create();

    $this->product = Product::factory()->create([
        'is_service' => true,
        'time_unit_enum' => TimeUnitEnum::Hours,
    ]);

    $this->workTime = WorkTime::factory()->create([
        'user_id' => $this->user->getKey(),
        'contact_id' => $this->contact->getKey(),
        'is_locked' => true,
        'is_daily_work_time' => false,
        'is_billable' => true,
        'started_at' => now()->subHours(2),
        'ended_at' => now(),
    ]);
});

test('creates new orders from work times', function (): void {
    $result = CreateOrdersFromWorkTimes::make([
        'product_id' => $this->product->getKey(),
        'order_type_id' => $this->orderType->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'round' => 'round',
        'work_times' => [['id' => $this->workTime->getKey()]],
    ])
        ->validate()
        ->execute();

    expect($result)->toHaveCount(1);

    $this->workTime->refresh();
    expect($this->workTime->order_position_id)->not->toBeNull();
});

test('adds positions to existing order when order_id provided', function (): void {
    $language = Language::query()->first();
    $currency = Currency::query()->where('is_default', true)->first();
    $priceList = PriceList::query()->first();

    $order = Order::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'language_id' => $language->getKey(),
        'order_type_id' => $this->orderType->getKey(),
        'payment_type_id' => $this->paymentType->getKey(),
        'price_list_id' => $priceList->getKey(),
        'currency_id' => $currency->getKey(),
        'address_invoice_id' => $this->contact->addresses->first()->getKey(),
        'address_delivery_id' => $this->contact->addresses->first()->getKey(),
        'is_locked' => false,
    ]);

    $result = CreateOrdersFromWorkTimes::make([
        'product_id' => $this->product->getKey(),
        'order_id' => $order->getKey(),
        'round' => 'round',
        'work_times' => [['id' => $this->workTime->getKey()]],
    ])
        ->validate()
        ->execute();

    expect($result)->toHaveCount(1)
        ->and($result->first()->getKey())->toBe($order->getKey());

    $this->workTime->refresh();
    expect($this->workTime->order_position_id)->not->toBeNull();

    $order->refresh();
    expect($order->orderPositions)->toHaveCount(1);
});

test('order_type_id and tenant_id not required when order_id provided', function (): void {
    $language = Language::query()->first();
    $currency = Currency::query()->where('is_default', true)->first();
    $priceList = PriceList::query()->first();

    $order = Order::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'language_id' => $language->getKey(),
        'order_type_id' => $this->orderType->getKey(),
        'payment_type_id' => $this->paymentType->getKey(),
        'price_list_id' => $priceList->getKey(),
        'currency_id' => $currency->getKey(),
        'address_invoice_id' => $this->contact->addresses->first()->getKey(),
        'address_delivery_id' => $this->contact->addresses->first()->getKey(),
        'is_locked' => false,
    ]);

    $result = CreateOrdersFromWorkTimes::make([
        'product_id' => $this->product->getKey(),
        'order_id' => $order->getKey(),
        'round' => 'round',
        'work_times' => [['id' => $this->workTime->getKey()]],
    ])
        ->validate()
        ->execute();

    expect($result)->toHaveCount(1);
});
