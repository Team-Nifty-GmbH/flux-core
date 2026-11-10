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
use FluxErp\Models\Warehouse;
use FluxErp\Models\WorkTime;
use Illuminate\Validation\ValidationException;

beforeEach(function (): void {
    $this->contact = Contact::factory()->create();

    $address = Address::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'is_main_address' => true,
    ]);

    $this->contact->update(['invoice_address_id' => $address->getKey()]);

    Language::factory()->create();
    Currency::factory()->create(['is_default' => true]);
    PriceList::factory()->create();
    Warehouse::factory()->create(['is_default' => true]);

    $this->orderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
    ]);

    $this->paymentType = PaymentType::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create();

    $this->product = Product::factory()->create([
        'is_service' => true,
        'time_unit_enum' => TimeUnitEnum::Hour,
    ]);

    $this->workTime = WorkTime::factory()->create([
        'user_id' => $this->user->getKey(),
        'contact_id' => $this->contact->getKey(),
        'is_locked' => true,
        'is_daily_work_time' => false,
        'is_billable' => true,
        'is_pause' => false,
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
    $order = Order::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'language_id' => Language::query()->first()->getKey(),
        'order_type_id' => $this->orderType->getKey(),
        'payment_type_id' => $this->paymentType->getKey(),
        'price_list_id' => PriceList::query()->first()->getKey(),
        'currency_id' => Currency::query()->where('is_default', true)->first()->getKey(),
        'address_invoice_id' => $this->contact->invoice_address_id,
        'address_delivery_id' => $this->contact->invoice_address_id,
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
    $order = Order::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'language_id' => Language::query()->first()->getKey(),
        'order_type_id' => $this->orderType->getKey(),
        'payment_type_id' => $this->paymentType->getKey(),
        'price_list_id' => PriceList::query()->first()->getKey(),
        'currency_id' => Currency::query()->where('is_default', true)->first()->getKey(),
        'address_invoice_id' => $this->contact->invoice_address_id,
        'address_delivery_id' => $this->contact->invoice_address_id,
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

test('rejects work times that have not ended yet', function (array $attributes): void {
    $unfinishedWorkTime = WorkTime::factory()->create(array_merge([
        'user_id' => $this->user->getKey(),
        'contact_id' => $this->contact->getKey(),
        'is_daily_work_time' => false,
        'is_billable' => true,
        'is_pause' => false,
    ], $attributes));

    expect(fn () => CreateOrdersFromWorkTimes::make([
        'product_id' => $this->product->getKey(),
        'order_type_id' => $this->orderType->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'round' => 'round',
        'work_times' => [
            ['id' => $this->workTime->getKey()],
            ['id' => $unfinishedWorkTime->getKey()],
        ],
    ])
        ->validate()
        ->execute()
    )->toThrow(ValidationException::class, 'Work times that have not ended yet can not be billed.');

    expect(Order::query()->count())->toBe(0)
        ->and($this->workTime->refresh()->order_position_id)->toBeNull()
        ->and($unfinishedWorkTime->refresh()->order_position_id)->toBeNull();
})->with([
    'ends in the future' => [[
        'is_locked' => true,
        'started_at' => now()->addHour(),
        'ended_at' => now()->addHours(3),
        'total_time_ms' => 7200000,
    ]],
    'running timer' => [[
        'is_locked' => false,
        'started_at' => now()->subHour(),
        'ended_at' => null,
        'total_time_ms' => 0,
    ]],
]);

test('ignores unfinished non billable work times when non billable ones are not billed', function (): void {
    $futureWorkTime = WorkTime::factory()->create([
        'user_id' => $this->user->getKey(),
        'contact_id' => $this->contact->getKey(),
        'is_locked' => true,
        'is_daily_work_time' => false,
        'is_billable' => false,
        'is_pause' => false,
        'started_at' => now()->addHour(),
        'ended_at' => now()->addHours(3),
        'total_time_ms' => 7200000,
    ]);

    $result = CreateOrdersFromWorkTimes::make([
        'product_id' => $this->product->getKey(),
        'order_type_id' => $this->orderType->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'round' => 'round',
        'add_non_billable_work_times' => false,
        'work_times' => [
            ['id' => $this->workTime->getKey()],
            ['id' => $futureWorkTime->getKey()],
        ],
    ])
        ->validate()
        ->execute();

    expect($result)->toHaveCount(1)
        ->and($this->workTime->refresh()->order_position_id)->not->toBeNull()
        ->and($futureWorkTime->refresh()->order_position_id)->toBeNull();
});
