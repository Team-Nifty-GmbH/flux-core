<?php

use FluxErp\Actions\Schedule\CreateSchedule;
use FluxErp\Actions\Schedule\UpdateSchedule;
use FluxErp\Invokable\ProcessSubscriptionOrder;
use FluxErp\Models\Schedule;

test('create schedule requires name and cron', function (): void {
    CreateSchedule::assertValidationErrors([], ['name', 'cron']);
});

test('a schedule with an unknown repeatable name can still be updated', function (): void {
    $schedule = Schedule::query()->create([
        'name' => 'Miete Buero Salzstrasse (Auftrag 941)',
        'class' => ProcessSubscriptionOrder::class,
        'type' => 'invokable',
        'cron' => [
            'methods' => ['basic' => 'monthlyOn', 'dayConstraint' => null, 'timeConstraint' => null],
            'parameters' => ['basic' => [1, '06:00'], 'dayConstraint' => [], 'timeConstraint' => []],
        ],
        'parameters' => ['orderId' => 1],
        'is_active' => true,
    ]);

    $updated = UpdateSchedule::make([
        'id' => $schedule->getKey(),
        'parameters' => ['orderId' => 2],
    ])
        ->validate()
        ->execute();

    expect(data_get($updated->parameters, 'orderId'))->toBe(2);
});

test('updating a schedule cannot clear its repetition', function (): void {
    $schedule = Schedule::query()->create([
        'name' => 'ProcessSubscriptionOrder',
        'class' => ProcessSubscriptionOrder::class,
        'type' => 'invokable',
        'cron' => [
            'methods' => ['basic' => 'yearlyOn', 'dayConstraint' => null, 'timeConstraint' => null],
            'parameters' => ['basic' => [4, 1, '06:00'], 'dayConstraint' => [], 'timeConstraint' => []],
        ],
        'parameters' => ['orderId' => 1],
        'is_active' => true,
    ]);

    UpdateSchedule::assertValidationErrors(
        [
            'id' => $schedule->getKey(),
            'cron' => [
                'methods' => ['basic' => null, 'dayConstraint' => null, 'timeConstraint' => null],
                'parameters' => ['basic' => [], 'dayConstraint' => [], 'timeConstraint' => []],
            ],
        ],
        ['cron.methods.basic']
    );

    expect(data_get($schedule->refresh()->cron, 'methods.basic'))->toBe('yearlyOn');
});

test('a schedule only accepts a valid timezone', function (): void {
    $schedule = Schedule::query()->create([
        'name' => 'ProcessSubscriptionOrder',
        'class' => ProcessSubscriptionOrder::class,
        'type' => 'invokable',
        'cron' => [
            'methods' => ['basic' => 'dailyAt', 'dayConstraint' => null, 'timeConstraint' => null],
            'parameters' => ['basic' => ['06:00'], 'dayConstraint' => [], 'timeConstraint' => []],
        ],
        'parameters' => ['orderId' => 1],
        'is_active' => true,
    ]);

    UpdateSchedule::assertValidationErrors(
        ['id' => $schedule->getKey(), 'timezone' => 'Europe/Nowhere'],
        ['timezone']
    );

    expect($schedule->refresh()->timezone)->toBeNull();

    $updated = UpdateSchedule::make(['id' => $schedule->getKey(), 'timezone' => 'Europe/Berlin'])
        ->validate()
        ->execute();

    expect($updated->timezone)->toBe('Europe/Berlin');
});

test('create schedule rejects an invalid timezone', function (): void {
    CreateSchedule::assertValidationErrors(['timezone' => 'Mars/Olympus'], ['timezone']);
});
