<?php

use Cron\CronExpression;
use FluxErp\Models\Schedule;
use Illuminate\Support\Carbon;

test('next run after an off tick due_at skips the same day tick of a daily cron', function (): void {
    $nextRun = Schedule::nextRunAfter(
        new CronExpression('0 6 1 2 *'),
        Carbon::create(2026, 2, 1, 0, 0, 0)
    );

    expect($nextRun->toDateTimeString())->toBe('2027-02-01 06:00:00');
});

test('next run after an off tick due_at keeps the next tick of a cron running several times a day', function (): void {
    $nextRun = Schedule::nextRunAfter(
        new CronExpression('0 */2 * * *'),
        Carbon::create(2026, 4, 1, 13, 17, 0)
    );

    expect($nextRun->toDateTimeString())->toBe('2026-04-01 14:00:00');
});
