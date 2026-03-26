<?php

use Carbon\Carbon;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Enums\TimeFrameEnum;
use FluxErp\Livewire\Widgets\MarginBySalesAgent;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\User;
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

    $this->agents = collect([
        User::factory()->create(['name' => 'Agent 1', 'language_id' => $language->getKey()]),
        User::factory()->create(['name' => 'Agent 2', 'language_id' => $language->getKey()]),
    ]);

    $this->orders = collect();

    foreach ($this->agents as $agent) {
        $this->orders = $this->orders
            ->merge(
                Order::factory()->count(2)->create([
                    'tenant_id' => $this->dbTenant->getKey(),
                    'agent_id' => $agent->getKey(),
                    'order_type_id' => $this->orderType->getKey(),
                    'language_id' => $language->getKey(),
                    'currency_id' => $currency->getKey(),
                    'price_list_id' => $priceList->getKey(),
                    'payment_type_id' => $paymentType->getKey(),
                    'address_invoice_id' => $address->getKey(),
                    'address_delivery_id' => $address->getKey(),
                    'margin' => 1000,
                    'order_date' => Carbon::now(),
                ])
            )
            ->merge(
                Order::factory()->count(2)->create([
                    'tenant_id' => $this->dbTenant->getKey(),
                    'agent_id' => $agent->getKey(),
                    'order_type_id' => $this->orderType->getKey(),
                    'language_id' => $language->getKey(),
                    'currency_id' => $currency->getKey(),
                    'price_list_id' => $priceList->getKey(),
                    'payment_type_id' => $paymentType->getKey(),
                    'address_invoice_id' => $address->getKey(),
                    'address_delivery_id' => $address->getKey(),
                    'margin' => 500,
                    'order_date' => Carbon::now()->startOfMonth(),
                ])
            );
    }
});

test('renders successfully', function (): void {
    Livewire::test(MarginBySalesAgent::class)
        ->assertOk();
});

test('timeframe in the future', function (): void {
    $start = Carbon::now()->addDay()->toDateString();
    $end = Carbon::now()->addDays(2)->toDateString();
    $timeFrame = TimeFrameEnum::Custom;

    Livewire::test(MarginBySalesAgent::class)
        ->set('timeFrame', $timeFrame)
        ->set('start', $start)
        ->set('end', $end)
        ->call('calculateChart')
        ->assertSet('series', [])
        ->assertOk()
        ->assertHasNoErrors();
});

test('timeframe this month', function (): void {
    $timeFrame = TimeFrameEnum::ThisMonth;

    $test = Livewire::test(MarginBySalesAgent::class)
        ->set('timeFrame', $timeFrame)
        ->call('calculateChart')
        ->assertOk()
        ->assertHasNoErrors();

    $series = $test->get('series');

    expect($series)->toBeArray();
    expect($series)->not->toBeEmpty();

    foreach ($series as $item) {
        expect($item)->toHaveKey('id');
        expect($item)->toHaveKey('name');
        expect($item)->toHaveKey('data');
    }
});

test('show method redirects correctly', function (): void {
    $params = [
        'id' => $this->agents[0]->getKey(),
        'name' => $this->agents[0]->name,
    ];

    Livewire::test(MarginBySalesAgent::class)
        ->call('show', $params)
        ->assertRedirect(route('orders.orders'));
});

test('options return expected structure', function (): void {
    $test = Livewire::test(MarginBySalesAgent::class)
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
        expect(data_get($option, 'params'))->toHaveKey('name');
    }
});
