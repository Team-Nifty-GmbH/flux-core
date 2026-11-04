<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Enums\TimeFrameEnum;
use FluxErp\Livewire\Widgets\RecurringRevenueForecast;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    $this->travelTo(now()->startOfMonth()->addHour());
});

test('renders successfully', function (): void {
    Livewire::test(RecurringRevenueForecast::class)
        ->assertOk();
});

test('forecasts a scheduled sales subscription', function (): void {
    $order = makeForecastOrder(OrderTypeEnum::Subscription, '100.00');

    $component = Livewire::test(RecurringRevenueForecast::class)
        ->call('calculateChart');

    expect(array_values(array_unique($component->get('orderIds'))))->toBe([$order->getKey()])
        ->and($component->get('series.0.data'))->toBe(['100.00']);
});

test('leaves purchase subscriptions out of the revenue', function (): void {
    $salesOrder = makeForecastOrder(OrderTypeEnum::Subscription, '100.00');
    makeForecastOrder(OrderTypeEnum::PurchaseSubscription, '40.00');

    $component = Livewire::test(RecurringRevenueForecast::class)
        ->call('calculateChart');

    expect(array_values(array_unique($component->get('orderIds'))))->toBe([$salesOrder->getKey()])
        ->and($component->get('series.0.data'))->toBe(['100.00']);
});

test('adds up several subscriptions falling on the same date', function (): void {
    makeForecastOrder(OrderTypeEnum::Subscription, '100.00');
    makeForecastOrder(OrderTypeEnum::Subscription, '25.50');

    $component = Livewire::test(RecurringRevenueForecast::class)
        ->call('calculateChart');

    expect($component->get('series.0.data'))->toBe(['125.50']);
});

test('leaves an inactive schedule out', function (): void {
    makeForecastOrder(OrderTypeEnum::Subscription, '100.00', scheduleAttributes: ['is_active' => false]);

    $component = Livewire::test(RecurringRevenueForecast::class)
        ->call('calculateChart');

    expect($component->get('series'))->toBeEmpty()
        ->and($component->get('xaxis.categories'))->toBeEmpty();
});

test('leaves a schedule out that has already ended', function (): void {
    makeForecastOrder(OrderTypeEnum::Subscription, '100.00', scheduleAttributes: [
        'ends_at' => now()->subDay(),
    ]);

    $component = Livewire::test(RecurringRevenueForecast::class)
        ->call('calculateChart');

    expect($component->get('series'))->toBeEmpty();
});

test('starts the forecast at a due date in the future', function (): void {
    makeForecastOrder(OrderTypeEnum::Subscription, '100.00', cronExpression: '0 0 15,20 * *', scheduleAttributes: [
        'due_at' => now()->startOfMonth()->addDays(19),
    ]);

    $component = Livewire::test(RecurringRevenueForecast::class)
        ->call('calculateChart');

    expect($component->get('xaxis.categories'))->toBe([now()->startOfMonth()->addDays(19)->format('Y-m-d')])
        ->and($component->get('series.0.data'))->toBe(['100.00']);
});

test('leaves a schedule out that is not due within the time frame', function (): void {
    makeForecastOrder(OrderTypeEnum::Subscription, '100.00', scheduleAttributes: [
        'due_at' => now()->addMonthNoOverflow()->startOfMonth()->addDays(14),
    ]);

    $component = Livewire::test(RecurringRevenueForecast::class)
        ->call('calculateChart');

    expect($component->get('series'))->toBeEmpty()
        ->and($component->get('orderIds'))->toBeEmpty();
});

test('forecasts the following runs after a due date outside the cron rhythm', function (): void {
    makeForecastOrder(OrderTypeEnum::Subscription, '100.00', cronExpression: '0 0 20 * *', scheduleAttributes: [
        'due_at' => now()->startOfMonth()->addDays(14)->addHours(12),
    ]);

    $component = Livewire::test(RecurringRevenueForecast::class)
        ->call('calculateChart');

    expect($component->get('xaxis.categories'))->toBe([
        now()->startOfMonth()->addDays(14)->format('Y-m-d'),
        now()->startOfMonth()->addDays(19)->format('Y-m-d'),
    ]);
});

test('does not count a due date twice when the cron matches later the same day', function (): void {
    makeForecastOrder(OrderTypeEnum::Subscription, '100.00', cronExpression: '0 6 15 * *', scheduleAttributes: [
        'due_at' => now()->startOfMonth()->addDays(14),
    ]);

    $component = Livewire::test(RecurringRevenueForecast::class)
        ->call('calculateChart');

    expect($component->get('series.0.data'))->toBe(['100.00']);
});

test('forecasts from the cron rhythm when the due date has passed', function (): void {
    makeForecastOrder(OrderTypeEnum::Subscription, '100.00', scheduleAttributes: [
        'due_at' => now()->subMinute(),
    ]);

    $component = Livewire::test(RecurringRevenueForecast::class)
        ->call('calculateChart');

    expect($component->get('xaxis.categories'))->toBe([now()->startOfMonth()->addDays(14)->format('Y-m-d')]);
});

test('forecasts every month end of a lastDayOfMonth schedule', function (
    string $cronExpression,
    array $expected
): void {
    $this->travelTo('2026-01-01 01:00:00');

    makeForecastOrder(OrderTypeEnum::Subscription, '100.00', cronExpression: $cronExpression, scheduleAttributes: [
        'cron' => [
            'methods' => ['basic' => 'lastDayOfMonth', 'dayConstraint' => null, 'timeConstraint' => null],
            'parameters' => ['basic' => ['06:00'], 'dayConstraint' => [], 'timeConstraint' => []],
        ],
    ]);

    $component = Livewire::test(RecurringRevenueForecast::class)
        ->set('timeFrame', TimeFrameEnum::ThisYear)
        ->call('calculateChart');

    expect(array_slice($component->get('xaxis.categories'), 0, 4))->toBe($expected);
})->with([
    'expression baked in a long month' => ['0 6 31 * *', ['2026-01-31', '2026-02-28', '2026-03-31', '2026-04-30']],
    'expression baked in a short month' => ['0 6 28 * *', ['2026-01-31', '2026-02-28', '2026-03-31', '2026-04-30']],
]);

test('leaves a schedule out that has run all its recurrences', function (): void {
    makeForecastOrder(OrderTypeEnum::Subscription, '100.00', scheduleAttributes: [
        'recurrences' => 3,
        'current_recurrence' => 3,
    ]);

    $component = Livewire::test(RecurringRevenueForecast::class)
        ->call('calculateChart');

    expect($component->get('series'))->toBeEmpty();
});
