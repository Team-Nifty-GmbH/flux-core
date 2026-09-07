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

test('leaves a schedule out that has run all its recurrences', function (): void {
    makeForecastOrder(OrderTypeEnum::Subscription, '100.00', scheduleAttributes: [
        'recurrences' => 3,
        'current_recurrence' => 3,
    ]);

    $component = Livewire::test(RecurringRevenueForecast::class)
        ->call('calculateChart');

    expect($component->get('series'))->toBeEmpty();
});
