<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Widgets\RecurringCostsForecast;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    $this->travelTo(now()->startOfMonth()->addHour());
});

test('renders successfully', function (): void {
    Livewire::test(RecurringCostsForecast::class)
        ->assertOk();
});

test('forecasts a scheduled purchase subscription', function (): void {
    $order = makeForecastOrder(OrderTypeEnum::PurchaseSubscription, '40.00');

    $component = Livewire::test(RecurringCostsForecast::class)
        ->call('calculateChart');

    expect(array_values(array_unique($component->get('orderIds'))))->toBe([$order->getKey()])
        ->and($component->get('series.0.data'))->toBe(['40.00']);
});

test('leaves sales subscriptions out of the costs', function (): void {
    $purchaseOrder = makeForecastOrder(OrderTypeEnum::PurchaseSubscription, '40.00');
    makeForecastOrder(OrderTypeEnum::Subscription, '100.00');

    $component = Livewire::test(RecurringCostsForecast::class)
        ->call('calculateChart');

    expect(array_values(array_unique($component->get('orderIds'))))->toBe([$purchaseOrder->getKey()])
        ->and($component->get('series.0.data'))->toBe(['40.00']);
});

test('adds up several purchase subscriptions falling on the same date', function (): void {
    makeForecastOrder(OrderTypeEnum::PurchaseSubscription, '40.00');
    makeForecastOrder(OrderTypeEnum::Purchase, '10.00');

    $component = Livewire::test(RecurringCostsForecast::class)
        ->call('calculateChart');

    expect($component->get('series.0.data'))->toBe(['50.00']);
});

test('leaves an inactive schedule out', function (): void {
    makeForecastOrder(OrderTypeEnum::PurchaseSubscription, '40.00', scheduleAttributes: ['is_active' => false]);

    $component = Livewire::test(RecurringCostsForecast::class)
        ->call('calculateChart');

    expect($component->get('series'))->toBeEmpty();
});

test('spreads the forecast over every date the schedule runs on', function (): void {
    makeForecastOrder(OrderTypeEnum::PurchaseSubscription, '40.00', cronExpression: '0 0 15,28 * *');

    $component = Livewire::test(RecurringCostsForecast::class)
        ->call('calculateChart');

    expect($component->get('series.0.data'))->toBe(['40.00', '40.00'])
        ->and($component->get('xaxis.categories'))->toHaveCount(2);
});
