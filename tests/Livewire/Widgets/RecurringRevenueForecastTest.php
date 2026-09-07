<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Enums\RepeatableTypeEnum;
use FluxErp\Invokable\ProcessSubscriptionOrder;
use FluxErp\Livewire\Widgets\RecurringCostsForecast;
use FluxErp\Livewire\Widgets\RecurringRevenueForecast;
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
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    $this->travelTo(now()->startOfMonth()->addHour());

    $this->tenant = Tenant::factory()->create();
    $currency = Currency::factory()->create(['is_default' => true]);
    $language = Language::factory()->create(['is_default' => true]);
    $priceList = PriceList::factory()->create(['is_default' => true]);
    $paymentType = PaymentType::factory()
        ->hasAttached(factory: $this->tenant, relationship: 'tenants')
        ->create(['is_default' => true]);
    $contact = Contact::factory()
        ->hasAttached(factory: $this->tenant, relationship: 'tenants')
        ->create();
    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
    ]);

    $this->scheduledOrder = function (OrderTypeEnum $orderTypeEnum, string $totalNetPrice) use (
        $currency,
        $language,
        $priceList,
        $paymentType,
        $contact,
        $address
    ): Order {
        $orderType = OrderType::factory()
            ->hasAttached(factory: $this->tenant, relationship: 'tenants')
            ->create([
                'order_type_enum' => $orderTypeEnum,
                'is_active' => true,
            ]);

        $order = Order::factory()->create([
            'tenant_id' => $this->tenant->getKey(),
            'contact_id' => $contact->getKey(),
            'address_invoice_id' => $address->getKey(),
            'order_type_id' => $orderType->getKey(),
            'currency_id' => $currency->getKey(),
            'language_id' => $language->getKey(),
            'price_list_id' => $priceList->getKey(),
            'payment_type_id' => $paymentType->getKey(),
            'total_net_price' => $totalNetPrice,
        ]);

        $schedule = Schedule::create([
            'uuid' => Str::uuid(),
            'name' => 'ProcessSubscriptionOrder',
            'class' => ProcessSubscriptionOrder::class,
            'type' => RepeatableTypeEnum::Invokable,
            'cron' => [
                'methods' => ['basic' => 'monthlyOn', 'dayConstraint' => null, 'timeConstraint' => null],
                'parameters' => ['basic' => [15, '00:00'], 'dayConstraint' => [], 'timeConstraint' => []],
            ],
            'cron_expression' => '0 0 15 * *',
            'is_active' => true,
            'parameters' => ['orderId' => $order->getKey()],
        ]);

        $order->schedules()->attach($schedule->getKey());

        return $order;
    };

    $this->salesOrder = ($this->scheduledOrder)(OrderTypeEnum::Subscription, '100.00');
    $this->purchaseOrder = ($this->scheduledOrder)(OrderTypeEnum::PurchaseSubscription, '40.00');
});

test('renders successfully', function (): void {
    Livewire::test(RecurringRevenueForecast::class)
        ->assertOk();
});

test('revenue forecast leaves purchase subscriptions out', function (): void {
    $component = Livewire::test(RecurringRevenueForecast::class)
        ->call('calculateChart');

    expect(array_values(array_unique($component->get('orderIds'))))->toBe([$this->salesOrder->getKey()])
        ->and($component->get('series.0.data'))->toBe(['100.00']);
});

test('costs forecast contains only purchase subscriptions', function (): void {
    $component = Livewire::test(RecurringCostsForecast::class)
        ->call('calculateChart');

    expect(array_values(array_unique($component->get('orderIds'))))->toBe([$this->purchaseOrder->getKey()])
        ->and($component->get('series.0.data'))->toBe(['40.00']);
});
