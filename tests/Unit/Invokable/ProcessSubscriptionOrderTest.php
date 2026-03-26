<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Enums\RepeatableTypeEnum;
use FluxErp\Invokable\ProcessSubscriptionOrder;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Schedule;
use FluxErp\Models\Tenant;
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
        ->hasAttached($this->tenant, relationship: 'tenants')
        ->create([
            'is_default' => true,
        ]);

    $this->contact = Contact::factory()->create([
        'tenant_id' => $this->tenant->getKey(),
    ]);

    $this->address = Address::factory()->create([
        'tenant_id' => $this->tenant->getKey(),
        'contact_id' => $this->contact->getKey(),
        'is_main_address' => true,
    ]);

    $this->subscriptionOrderType = OrderType::factory()->create([
        'tenant_id' => $this->tenant->getKey(),
        'order_type_enum' => OrderTypeEnum::Subscription,
        'is_active' => true,
    ]);

    $this->targetOrderType = OrderType::factory()->create([
        'tenant_id' => $this->tenant->getKey(),
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
    ]);

    $this->subscriptionOrder = Order::factory()->create([
        'tenant_id' => $this->tenant->getKey(),
        'contact_id' => $this->contact->getKey(),
        'address_invoice_id' => $this->address->getKey(),
        'order_type_id' => $this->subscriptionOrderType->getKey(),
        'currency_id' => $this->currency->getKey(),
        'language_id' => $this->language->getKey(),
        'price_list_id' => $this->priceList->getKey(),
        'payment_type_id' => $this->paymentType->getKey(),
        'parent_id' => null,
    ]);
});

test('process subscription order sets created_from_id but not parent_id', function (): void {
    $processor = new ProcessSubscriptionOrder();

    $result = $processor(
        orderId: $this->subscriptionOrder->getKey(),
        orderTypeId: $this->targetOrderType->getKey()
    );

    expect($result)->toBeTrue();

    $newOrder = Order::query()
        ->where('created_from_id', $this->subscriptionOrder->getKey())
        ->first();

    expect($newOrder)->not->toBeNull()
        ->and($newOrder->created_from_id)->toBe($this->subscriptionOrder->getKey())
        ->and($newOrder->parent_id)->toBeNull();
});

test('process subscription order returns false for non-subscription order type', function (): void {
    $regularOrderType = OrderType::factory()->create([
        'tenant_id' => $this->tenant->getKey(),
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
    ]);

    $regularOrder = Order::factory()->create([
        'tenant_id' => $this->tenant->getKey(),
        'contact_id' => $this->contact->getKey(),
        'address_invoice_id' => $this->address->getKey(),
        'order_type_id' => $regularOrderType->getKey(),
        'currency_id' => $this->currency->getKey(),
        'language_id' => $this->language->getKey(),
        'price_list_id' => $this->priceList->getKey(),
        'payment_type_id' => $this->paymentType->getKey(),
    ]);

    $processor = new ProcessSubscriptionOrder();

    $result = $processor(
        orderId: $regularOrder->getKey(),
        orderTypeId: $this->targetOrderType->getKey()
    );

    expect($result)->toBeFalse();
});

test('process subscription order throws exception on validation error', function (): void {
    // Create a contact in a different tenant to cause ExistsWithForeign validation to fail
    $otherTenant = Tenant::factory()->create();
    $otherContact = Contact::factory()->create([
        'tenant_id' => $otherTenant->getKey(),
    ]);

    // Update order to use contact from different tenant (bypassing FK by using raw query)
    Illuminate\Support\Facades\DB::table('orders')
        ->where('id', $this->subscriptionOrder->getKey())
        ->update(['contact_id' => $otherContact->getKey()]);

    $processor = new ProcessSubscriptionOrder();

    $processor(
        orderId: $this->subscriptionOrder->getKey(),
        orderTypeId: $this->targetOrderType->getKey()
    );
})->throws(Illuminate\Validation\ValidationException::class);

test('process subscription order sets correct performance period from schedule', function (): void {
    $orderDate = now()->startOfYear();

    $this->subscriptionOrder->update([
        'order_date' => $orderDate,
        'system_delivery_date' => null,
        'system_delivery_date_end' => null,
    ]);

    $schedule = Schedule::create([
        'uuid' => Illuminate\Support\Str::uuid(),
        'name' => 'ProcessSubscriptionOrder',
        'class' => ProcessSubscriptionOrder::class,
        'type' => RepeatableTypeEnum::Invokable,
        'cron' => [
            'methods' => [
                'basic' => 'yearly',
                'dayConstraint' => null,
                'timeConstraint' => null,
            ],
            'parameters' => [
                'basic' => [],
                'dayConstraint' => [],
                'timeConstraint' => [],
            ],
        ],
        'cron_expression' => '0 0 1 1 *',
        'is_active' => true,
        'parameters' => [
            'orderId' => $this->subscriptionOrder->getKey(),
            'orderTypeId' => $this->targetOrderType->getKey(),
        ],
    ]);

    $this->subscriptionOrder->schedules()->attach($schedule->getKey());

    $processor = new ProcessSubscriptionOrder();

    $result = $processor(
        orderId: $this->subscriptionOrder->getKey(),
        orderTypeId: $this->targetOrderType->getKey()
    );

    expect($result)->toBeTrue();

    $newOrder = Order::query()
        ->where('created_from_id', $this->subscriptionOrder->getKey())
        ->first();

    expect($newOrder)->not->toBeNull()
        ->and($newOrder->system_delivery_date->format('Y-m-d'))->toBe($orderDate->format('Y-m-d'))
        ->and($newOrder->system_delivery_date_end->format('Y-m-d'))->toBe($orderDate->copy()->endOfYear()->format('Y-m-d'));
});

test('process subscription order sets correct performance period for monthly schedule', function (): void {
    $orderDate = now()->startOfMonth();

    $this->subscriptionOrder->update([
        'order_date' => $orderDate,
        'system_delivery_date' => null,
        'system_delivery_date_end' => null,
    ]);

    $schedule = Schedule::create([
        'uuid' => Illuminate\Support\Str::uuid(),
        'name' => 'ProcessSubscriptionOrder',
        'class' => ProcessSubscriptionOrder::class,
        'type' => RepeatableTypeEnum::Invokable,
        'cron' => [
            'methods' => [
                'basic' => 'monthly',
                'dayConstraint' => null,
                'timeConstraint' => null,
            ],
            'parameters' => [
                'basic' => [],
                'dayConstraint' => [],
                'timeConstraint' => [],
            ],
        ],
        'cron_expression' => '0 0 1 * *',
        'is_active' => true,
        'parameters' => [
            'orderId' => $this->subscriptionOrder->getKey(),
            'orderTypeId' => $this->targetOrderType->getKey(),
        ],
    ]);

    $this->subscriptionOrder->schedules()->attach($schedule->getKey());

    $processor = new ProcessSubscriptionOrder();

    $result = $processor(
        orderId: $this->subscriptionOrder->getKey(),
        orderTypeId: $this->targetOrderType->getKey()
    );

    expect($result)->toBeTrue();

    $newOrder = Order::query()
        ->where('created_from_id', $this->subscriptionOrder->getKey())
        ->first();

    expect($newOrder)->not->toBeNull()
        ->and($newOrder->system_delivery_date->format('Y-m-d'))->toBe($orderDate->format('Y-m-d'))
        ->and($newOrder->system_delivery_date_end->format('Y-m-d'))->toBe($orderDate->copy()->endOfMonth()->format('Y-m-d'));
});

test('process subscription order sets correct performance period for quarterly schedule', function (): void {
    $orderDate = now()->startOfQuarter();

    $this->subscriptionOrder->update([
        'order_date' => $orderDate,
        'system_delivery_date' => null,
        'system_delivery_date_end' => null,
    ]);

    $schedule = Schedule::create([
        'uuid' => Illuminate\Support\Str::uuid(),
        'name' => 'ProcessSubscriptionOrder',
        'class' => ProcessSubscriptionOrder::class,
        'type' => RepeatableTypeEnum::Invokable,
        'cron' => [
            'methods' => [
                'basic' => 'quarterly',
                'dayConstraint' => null,
                'timeConstraint' => null,
            ],
            'parameters' => [
                'basic' => [],
                'dayConstraint' => [],
                'timeConstraint' => [],
            ],
        ],
        'cron_expression' => '0 0 1 1,4,7,10 *',
        'is_active' => true,
        'parameters' => [
            'orderId' => $this->subscriptionOrder->getKey(),
            'orderTypeId' => $this->targetOrderType->getKey(),
        ],
    ]);

    $this->subscriptionOrder->schedules()->attach($schedule->getKey());

    $processor = new ProcessSubscriptionOrder();

    $result = $processor(
        orderId: $this->subscriptionOrder->getKey(),
        orderTypeId: $this->targetOrderType->getKey()
    );

    expect($result)->toBeTrue();

    $newOrder = Order::query()
        ->where('created_from_id', $this->subscriptionOrder->getKey())
        ->first();

    expect($newOrder)->not->toBeNull()
        ->and($newOrder->system_delivery_date->format('Y-m-d'))->toBe($orderDate->format('Y-m-d'))
        ->and($newOrder->system_delivery_date_end->format('Y-m-d'))->toBe($orderDate->copy()->endOfQuarter()->format('Y-m-d'));
});

test('process subscription order calculates next period from existing child order', function (): void {
    $orderDate = now()->startOfYear();

    $this->subscriptionOrder->update([
        'order_date' => $orderDate,
        'system_delivery_date' => $orderDate,
        'system_delivery_date_end' => $orderDate->copy()->endOfYear(),
    ]);

    // Create an existing child order linked via created_from_id (not parent_id)
    $existingChild = Order::factory()->create([
        'tenant_id' => $this->tenant->getKey(),
        'contact_id' => $this->contact->getKey(),
        'address_invoice_id' => $this->address->getKey(),
        'order_type_id' => $this->targetOrderType->getKey(),
        'currency_id' => $this->currency->getKey(),
        'language_id' => $this->language->getKey(),
        'price_list_id' => $this->priceList->getKey(),
        'payment_type_id' => $this->paymentType->getKey(),
        'created_from_id' => $this->subscriptionOrder->getKey(),
        'parent_id' => null,
        'system_delivery_date' => $orderDate,
        'system_delivery_date_end' => $orderDate->copy()->endOfYear(),
    ]);

    $schedule = Schedule::create([
        'uuid' => Illuminate\Support\Str::uuid(),
        'name' => 'ProcessSubscriptionOrder',
        'class' => ProcessSubscriptionOrder::class,
        'type' => RepeatableTypeEnum::Invokable,
        'cron' => [
            'methods' => [
                'basic' => 'yearly',
                'dayConstraint' => null,
                'timeConstraint' => null,
            ],
            'parameters' => [
                'basic' => [],
                'dayConstraint' => [],
                'timeConstraint' => [],
            ],
        ],
        'cron_expression' => '0 0 1 1 *',
        'is_active' => true,
        'parameters' => [
            'orderId' => $this->subscriptionOrder->getKey(),
            'orderTypeId' => $this->targetOrderType->getKey(),
        ],
    ]);

    $this->subscriptionOrder->schedules()->attach($schedule->getKey());

    $processor = new ProcessSubscriptionOrder();

    $result = $processor(
        orderId: $this->subscriptionOrder->getKey(),
        orderTypeId: $this->targetOrderType->getKey()
    );

    expect($result)->toBeTrue();

    $nextYear = $orderDate->copy()->addYear();

    $newOrder = Order::query()
        ->where('created_from_id', $this->subscriptionOrder->getKey())
        ->whereKeyNot($existingChild->getKey())
        ->first();

    // Next period should start the day after the existing child's end date
    expect($newOrder)->not->toBeNull()
        ->and($newOrder->system_delivery_date->format('Y-m-d'))->toBe($nextYear->format('Y-m-d'))
        ->and($newOrder->system_delivery_date_end->format('Y-m-d'))->toBe($nextYear->copy()->endOfYear()->format('Y-m-d'));
});
