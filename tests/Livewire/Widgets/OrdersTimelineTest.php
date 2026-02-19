<?php

use Carbon\Carbon;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Enums\TimeFrameEnum;
use FluxErp\Livewire\Widgets\OrdersTimeline;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use Livewire\Livewire;

beforeEach(function (): void {
    $contact = Contact::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    $address = Address::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $contact->getKey(),
    ]);

    $currency = Currency::factory()->create();
    $language = Language::factory()->create();
    $priceList = PriceList::factory()->create();

    $paymentType = PaymentType::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create();

    $this->orderType = OrderType::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'is_active' => true,
        'order_type_enum' => OrderTypeEnum::Order,
    ]);

    $this->orders = collect();

    $this->orders = $this->orders
        ->merge(
            Order::factory()->count(3)->create([
                'tenant_id' => $this->dbTenant->getKey(),
                'order_type_id' => $this->orderType->getKey(),
                'language_id' => $language->getKey(),
                'currency_id' => $currency->getKey(),
                'price_list_id' => $priceList->getKey(),
                'payment_type_id' => $paymentType->getKey(),
                'address_invoice_id' => $address->getKey(),
                'address_delivery_id' => $address->getKey(),
                'system_delivery_date' => Carbon::now(),
                'system_delivery_date_end' => Carbon::now()->addDays(5),
            ])
        )
        ->merge(
            Order::factory()->count(2)->create([
                'tenant_id' => $this->dbTenant->getKey(),
                'order_type_id' => $this->orderType->getKey(),
                'language_id' => $language->getKey(),
                'currency_id' => $currency->getKey(),
                'price_list_id' => $priceList->getKey(),
                'payment_type_id' => $paymentType->getKey(),
                'address_invoice_id' => $address->getKey(),
                'address_delivery_id' => $address->getKey(),
                'system_delivery_date' => Carbon::now()->startOfMonth(),
                'system_delivery_date_end' => Carbon::now()->startOfMonth()->addDays(10),
            ])
        );
});

test('renders successfully', function (): void {
    Livewire::test(OrdersTimeline::class)
        ->assertOk();
});

test('timeframe in the future', function (): void {
    $start = Carbon::now()->addMonth()->toDateString();
    $end = Carbon::now()->addMonths(2)->toDateString();
    $timeFrame = TimeFrameEnum::Custom;

    Livewire::test(OrdersTimeline::class)
        ->set('timeFrame', $timeFrame)
        ->set('start', $start)
        ->set('end', $end)
        ->call('calculateChart')
        ->assertOk()
        ->assertHasNoErrors();
});

test('timeframe this month', function (): void {
    $timeFrame = TimeFrameEnum::ThisMonth;

    $test = Livewire::test(OrdersTimeline::class)
        ->set('timeFrame', $timeFrame)
        ->call('calculateChart')
        ->assertOk()
        ->assertHasNoErrors();

    $series = $test->get('series');
    $data = $test->get('data');

    expect($series)->toBeArray();
    expect($data)->toBeArray();
});

test('options return expected structure', function (): void {
    $test = Livewire::test(OrdersTimeline::class)
        ->set('timeFrame', TimeFrameEnum::ThisMonth)
        ->call('calculateChart');

    $options = $test->instance()->options();

    expect($options)->toBeArray();

    foreach ($options as $option) {
        expect($option)->toHaveKey('label');
        expect($option)->toHaveKey('method');
        expect(data_get($option, 'method'))->toEqual('show');
        expect($option)->toHaveKey('params');
        expect(data_get($option, 'params'))->toHaveKey('id');
    }
});

test('series contains correct structure for timeline chart', function (): void {
    $timeFrame = TimeFrameEnum::ThisMonth;

    $test = Livewire::test(OrdersTimeline::class)
        ->set('timeFrame', $timeFrame)
        ->call('calculateChart')
        ->assertOk();

    $series = $test->get('series');

    expect($series)->toBeArray();
    expect($series)->toHaveCount(1);

    $seriesData = data_get($series, '0.data');

    expect($seriesData)->toBeArray();

    foreach ($seriesData as $item) {
        expect($item)->toHaveKey('x');
        expect($item)->toHaveKey('y');
        expect($item)->toHaveKey('fillColor');
        expect(data_get($item, 'y'))->toBeArray();
        expect(data_get($item, 'y'))->toHaveCount(2);
    }
});
