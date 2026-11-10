<?php

use FluxErp\Enums\OrderTypeEnum;
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

test('leaves a schedule out that has run all its recurrences', function (): void {
    makeForecastOrder(OrderTypeEnum::Subscription, '100.00', scheduleAttributes: [
        'recurrences' => 3,
        'current_recurrence' => 3,
    ]);

    $component = Livewire::test(RecurringRevenueForecast::class)
        ->call('calculateChart');

    expect($component->get('series'))->toBeEmpty();
});

test('leaves a run out that falls exactly on the end of the schedule', function (): void {
    makeForecastOrder(OrderTypeEnum::Subscription, '100.00', scheduleAttributes: [
        'ends_at' => now()->startOfMonth()->addDays(14),
    ]);

    $component = Livewire::test(RecurringRevenueForecast::class)
        ->call('calculateChart');

    expect($component->get('series'))->toBeEmpty()
        ->and($component->get('orderIds'))->toBeEmpty();
});

test('keeps a run that falls before the end of the schedule', function (): void {
    $order = makeForecastOrder(OrderTypeEnum::Subscription, '100.00', scheduleAttributes: [
        'ends_at' => now()->startOfMonth()->addDays(14)->addMinute(),
    ]);

    $component = Livewire::test(RecurringRevenueForecast::class)
        ->call('calculateChart');

    expect(array_values(array_unique($component->get('orderIds'))))->toBe([$order->getKey()])
        ->and($component->get('series.0.data'))->toBe(['100.00']);
});

test('forecasts only the recurrences that are left', function (): void {
    makeForecastOrder(
        OrderTypeEnum::Subscription,
        '100.00',
        cronExpression: '0 0 15,20,25 * *',
        scheduleAttributes: [
            'recurrences' => 3,
            'current_recurrence' => 2,
        ]
    );

    $component = Livewire::test(RecurringRevenueForecast::class)
        ->call('calculateChart');

    expect($component->get('series.0.data'))->toBe(['100.00']);
});
