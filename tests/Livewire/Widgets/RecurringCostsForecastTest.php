<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Enums\TimeFrameEnum;
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

test('starts the forecast at a due date in the future', function (): void {
    makeForecastOrder(
        OrderTypeEnum::PurchaseSubscription,
        '40.00',
        cronExpression: '0 0 15,20 * *',
        scheduleAttributes: [
            'due_at' => now()->startOfMonth()->addDays(19),
        ]
    );

    $component = Livewire::test(RecurringCostsForecast::class)
        ->call('calculateChart');

    expect($component->get('xaxis.categories'))->toBe([now()->startOfMonth()->addDays(19)->format('Y-m-d')]);
});

test('forecasts every month end of a lastDayOfMonth schedule', function (
    string $cronExpression,
    array $expected
): void {
    $this->travelTo('2026-01-01 01:00:00');

    makeForecastOrder(OrderTypeEnum::PurchaseSubscription, '40.00', cronExpression: $cronExpression, scheduleAttributes: [
        'cron' => [
            'methods' => ['basic' => 'lastDayOfMonth', 'dayConstraint' => null, 'timeConstraint' => null],
            'parameters' => ['basic' => ['06:00'], 'dayConstraint' => [], 'timeConstraint' => []],
        ],
    ]);

    $component = Livewire::test(RecurringCostsForecast::class)
        ->set('timeFrame', TimeFrameEnum::ThisYear)
        ->call('calculateChart');

    expect(array_slice($component->get('xaxis.categories'), 0, 4))->toBe($expected);
})->with([
    'expression baked in a long month' => ['0 6 31 * *', ['2026-01-31', '2026-02-28', '2026-03-31', '2026-04-30']],
    'expression baked in a short month' => ['0 6 28 * *', ['2026-01-31', '2026-02-28', '2026-03-31', '2026-04-30']],
]);

test('spreads the forecast over every date the schedule runs on', function (): void {
    makeForecastOrder(OrderTypeEnum::PurchaseSubscription, '40.00', cronExpression: '0 0 15,28 * *');

    $component = Livewire::test(RecurringCostsForecast::class)
        ->call('calculateChart');

    expect($component->get('series.0.data'))->toBe(['40.00', '40.00'])
        ->and($component->get('xaxis.categories'))->toHaveCount(2);
});
