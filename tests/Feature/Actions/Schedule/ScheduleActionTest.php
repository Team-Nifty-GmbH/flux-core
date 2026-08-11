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
