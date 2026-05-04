<?php

use FluxErp\Enums\RepeatableTypeEnum;
use FluxErp\Facades\Repeatable as RepeatableFacade;
use FluxErp\Models\Schedule;
use FluxErp\Tests\Feature\Console\ScheduleRunTestDefaultCronInvokable;
use FluxErp\Tests\Feature\Console\ScheduleRunTestFailingInvokable;
use FluxErp\Tests\Feature\Console\ScheduleRunTestInvokable;
use FluxErp\Tests\Feature\Console\ScheduleRunTestJob;
use FluxErp\Tests\Feature\Console\ScheduleRunTestSuccessJob;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;

test('does not set last_success when a scheduled job fails', function (): void {
    Queue::fake();

    $this->travelTo(Carbon::create(2025, 6, 15, 10, 0, 0));

    $schedule = resolve_static(Schedule::class, 'query')->create([
        'name' => 'Test Job Schedule',
        'class' => ScheduleRunTestJob::class,
        'type' => RepeatableTypeEnum::Job,
        'cron' => [
            'methods' => [
                'basic' => 'everyMinute',
                'dayConstraint' => null,
                'timeConstraint' => null,
            ],
            'parameters' => [
                'basic' => [],
                'dayConstraint' => [],
                'timeConstraint' => [null, null],
            ],
        ],
        'is_active' => true,
        'due_at' => now()->subMinute(),
    ]);

    $this->artisan('schedule:run');

    $schedule->refresh();

    expect($schedule->last_run)->not->toBeNull()
        ->and($schedule->last_success)->toBeNull();
});

test('sets last_success when a scheduled job completes successfully', function (): void {
    config()->set('queue.default', 'sync');

    $this->travelTo(Carbon::create(2025, 6, 15, 10, 0, 0));

    $schedule = resolve_static(Schedule::class, 'query')->create([
        'name' => 'Test Success Job Schedule',
        'class' => ScheduleRunTestSuccessJob::class,
        'type' => RepeatableTypeEnum::Job,
        'cron' => [
            'methods' => [
                'basic' => 'everyMinute',
                'dayConstraint' => null,
                'timeConstraint' => null,
            ],
            'parameters' => [
                'basic' => [],
                'dayConstraint' => [],
                'timeConstraint' => [null, null],
            ],
        ],
        'is_active' => true,
        'due_at' => now()->subMinute(),
    ]);

    $this->artisan('schedule:run');

    $schedule->refresh();

    expect($schedule->last_run)->not->toBeNull()
        ->and($schedule->last_success)->not->toBeNull();
});

test('increments current_recurrence when a scheduled job with recurrences completes', function (): void {
    config()->set('queue.default', 'sync');

    $this->travelTo(Carbon::create(2025, 6, 15, 10, 0, 0));

    $schedule = resolve_static(Schedule::class, 'query')->create([
        'name' => 'Test Recurrence Job Schedule',
        'class' => ScheduleRunTestSuccessJob::class,
        'type' => RepeatableTypeEnum::Job,
        'cron' => [
            'methods' => [
                'basic' => 'everyMinute',
                'dayConstraint' => null,
                'timeConstraint' => null,
            ],
            'parameters' => [
                'basic' => [],
                'dayConstraint' => [],
                'timeConstraint' => [null, null],
            ],
        ],
        'is_active' => true,
        'due_at' => now()->subMinute(),
        'recurrences' => 10,
        'current_recurrence' => 3,
    ]);

    $this->artisan('schedule:run');

    $schedule->refresh();

    expect($schedule->last_success)->not->toBeNull()
        ->and($schedule->current_recurrence)->toBe(4);
});

test('sets due_at to last day of next month for lastDayOfMonth schedules', function (): void {
    $this->travelTo(Carbon::create(2025, 1, 31, 0, 0, 0));

    $schedule = resolve_static(Schedule::class, 'query')->create([
        'name' => 'End of Month Schedule',
        'class' => ScheduleRunTestInvokable::class,
        'type' => RepeatableTypeEnum::Invokable,
        'cron' => [
            'methods' => [
                'basic' => 'lastDayOfMonth',
                'dayConstraint' => null,
                'timeConstraint' => null,
            ],
            'parameters' => [
                'basic' => ['0:0'],
                'dayConstraint' => [],
                'timeConstraint' => [null, null],
            ],
        ],
        'is_active' => true,
        'due_at' => now()->subDay(),
    ]);

    $this->artisan('schedule:run');

    $schedule->refresh();

    // Should be Feb 28, 2025 (last day of next month)
    // NOT March 31, 2025 (next occurrence of day 31)
    expect($schedule->due_at->format('Y-m-d'))->toBe('2025-02-28');
});

test('does not double fire overdue schedule when cron matches later the same day', function (): void {
    ScheduleRunTestInvokable::$invocationCount = 0;

    // Schedule: yearlyOn Feb 1 at 06:00, but due_at is midnight Feb 1
    // This simulates the migration setting due_at to a date without matching the cron time
    $this->travelTo(Carbon::create(2026, 2, 1, 0, 0, 10));

    $schedule = resolve_static(Schedule::class, 'query')->create([
        'name' => 'Overdue Double Fire Test',
        'class' => ScheduleRunTestInvokable::class,
        'type' => RepeatableTypeEnum::Invokable,
        'cron' => [
            'methods' => [
                'basic' => 'yearlyOn',
                'dayConstraint' => null,
                'timeConstraint' => null,
            ],
            'parameters' => [
                'basic' => [2, 1, '06:00'],
                'dayConstraint' => [],
                'timeConstraint' => [],
            ],
        ],
        'is_active' => true,
        'due_at' => Carbon::create(2026, 2, 1, 0, 0, 0),
    ]);

    // Run at midnight - should fire as overdue (cron is 06:00, not matching)
    $this->artisan('schedule:run');

    expect(ScheduleRunTestInvokable::$invocationCount)->toBe(1);

    $schedule->refresh();

    // due_at should be advanced past the 06:00 cron match to next year
    expect($schedule->due_at->toDateTimeString())->toContain('2027')
        ->and($schedule->due_at->greaterThan(Carbon::create(2026, 2, 1, 6, 0, 0)))->toBeTrue();

    // Travel to 06:00 and run again - should NOT fire
    // Reset the Schedule singleton to simulate a fresh process (like in production)
    $this->app->forgetInstance(Illuminate\Console\Scheduling\Schedule::class);
    $this->app->singleton(Illuminate\Console\Scheduling\Schedule::class, fn () => new Illuminate\Console\Scheduling\Schedule());

    $this->travelTo(Carbon::create(2026, 2, 1, 6, 0, 0));
    $this->artisan('schedule:run');

    expect(ScheduleRunTestInvokable::$invocationCount)->toBe(1);
});

test('advances due_at by one cycle when monthly cron is delayed into the next minute', function (): void {
    ScheduleRunTestInvokable::$invocationCount = 0;

    // Production scenario: ~130 monthly subscription schedules at the 1st of the month
    // take longer than 1 minute to process serially. Schedules processed after 00:00:59
    // hit the "overdue" branch because the cron `0 0 1 * *` no longer matches at 00:01.
    $this->travelTo(Carbon::create(2026, 4, 1, 0, 1, 5));

    $schedule = resolve_static(Schedule::class, 'query')->create([
        'name' => 'Monthly Subscription Schedule',
        'class' => ScheduleRunTestInvokable::class,
        'type' => RepeatableTypeEnum::Invokable,
        'cron' => [
            'methods' => [
                'basic' => 'monthly',
                'dayConstraint' => null,
                'timeConstraint' => null,
            ],
            'parameters' => [
                'basic' => [],
                'dayConstraint' => [],
                'timeConstraint' => [null, null],
            ],
        ],
        'is_active' => true,
        'due_at' => Carbon::create(2026, 4, 1, 0, 0, 0),
    ]);

    $this->artisan('schedule:run');

    expect(ScheduleRunTestInvokable::$invocationCount)->toBe(1);

    $schedule->refresh();

    // Next run should be 2026-05-01 (one month from due_at), NOT 2026-06-01.
    // The previous code incorrectly advanced $nextRunDate twice when overdue,
    // skipping a whole month of subscription invoices.
    expect($schedule->due_at->toDateTimeString())->toBe('2026-05-01 00:00:00');
});

test('does not execute the same schedule twice when due_at was advanced concurrently', function (): void {
    ScheduleRunTestInvokable::$invocationCount = 0;

    // Production scenario: process A runs schedule:run at 00:00, takes >1 minute to
    // process all schedules. Process B starts at 00:01 (next cron tick) and queries
    // the DB before A wrote the new due_at, so B's in-memory $repeatable still has
    // the past due_at. Once A releases the per-event mutex, B can acquire it and
    // would run the same schedule a second time, creating duplicate invoices.
    //
    // We simulate this by rolling the DB row's due_at back to a past value AFTER the
    // first successful run. The fix must detect "this cycle already ran" via the
    // schedule's last_run timestamp rather than relying on in-memory due_at alone.
    $this->travelTo(Carbon::create(2026, 4, 1, 0, 1, 5));

    $schedule = resolve_static(Schedule::class, 'query')->create([
        'name' => 'Concurrent Run Schedule',
        'class' => ScheduleRunTestInvokable::class,
        'type' => RepeatableTypeEnum::Invokable,
        'cron' => [
            'methods' => [
                'basic' => 'monthly',
                'dayConstraint' => null,
                'timeConstraint' => null,
            ],
            'parameters' => [
                'basic' => [],
                'dayConstraint' => [],
                'timeConstraint' => [null, null],
            ],
        ],
        'is_active' => true,
        'due_at' => Carbon::create(2026, 4, 1, 0, 0, 0),
    ]);

    $this->artisan('schedule:run');

    expect(ScheduleRunTestInvokable::$invocationCount)->toBe(1);

    // Simulate a second scheduler instance whose cached view of $repeatable still has
    // the original past due_at: the only way to bring the schedule back into the
    // due-set is to roll due_at back. last_run stays at the value Process A wrote.
    Illuminate\Support\Facades\DB::table('schedules')
        ->where('id', $schedule->getKey())
        ->update(['due_at' => Carbon::create(2026, 4, 1, 0, 0, 0)->toDateTimeString()]);

    $this->app->forgetInstance(Illuminate\Console\Scheduling\Schedule::class);
    $this->app->singleton(
        Illuminate\Console\Scheduling\Schedule::class,
        fn () => new Illuminate\Console\Scheduling\Schedule()
    );

    $this->artisan('schedule:run');

    // The schedule must not run a second time for the same cron cycle.
    expect(ScheduleRunTestInvokable::$invocationCount)->toBe(1);
});

test('runs a repeatable with defaultCron without a db entry', function (): void {
    ScheduleRunTestDefaultCronInvokable::$wasInvoked = false;

    RepeatableFacade::register(
        ScheduleRunTestDefaultCronInvokable::name(),
        ScheduleRunTestDefaultCronInvokable::class
    );

    $this->travelTo(Carbon::create(2025, 6, 15, 10, 0, 0));

    $this->artisan('schedule:run');

    expect(ScheduleRunTestDefaultCronInvokable::$wasInvoked)->toBeTrue();
});

test('uses db entry over defaultCron when both exist', function (): void {
    ScheduleRunTestDefaultCronInvokable::$wasInvoked = false;

    RepeatableFacade::register(
        ScheduleRunTestDefaultCronInvokable::name(),
        ScheduleRunTestDefaultCronInvokable::class
    );

    $this->travelTo(Carbon::create(2025, 6, 15, 10, 0, 0));

    // Create a DB entry for the same class with a yearly cron (won't be due now)
    resolve_static(Schedule::class, 'query')
        ->create([
            'name' => 'DB Default Cron Override',
            'class' => ScheduleRunTestDefaultCronInvokable::class,
            'type' => RepeatableTypeEnum::Invokable,
            'cron' => [
                'methods' => [
                    'basic' => 'yearly',
                    'dayConstraint' => null,
                    'timeConstraint' => null,
                ],
                'parameters' => [
                    'basic' => [],
                    'dayConstraint' => [],
                    'timeConstraint' => [null, null],
                ],
            ],
            'is_active' => true,
            'due_at' => now()->addYear(),
        ]);

    $this->artisan('schedule:run');

    // The defaultCron (everyMinute) should NOT have been used,
    // the DB entry (yearly, not due) takes precedence
    expect(ScheduleRunTestDefaultCronInvokable::$wasInvoked)->toBeFalse();
});

test('advances due_at when an invokable schedule fails', function (): void {
    ScheduleRunTestFailingInvokable::$invocationCount = 0;

    $this->travelTo(Carbon::create(2025, 6, 15, 10, 0, 0));

    $schedule = resolve_static(Schedule::class, 'query')->create([
        'name' => 'Failing Invokable Schedule',
        'class' => ScheduleRunTestFailingInvokable::class,
        'type' => RepeatableTypeEnum::Invokable,
        'cron' => [
            'methods' => [
                'basic' => 'everyMinute',
                'dayConstraint' => null,
                'timeConstraint' => null,
            ],
            'parameters' => [
                'basic' => [],
                'dayConstraint' => [],
                'timeConstraint' => [null, null],
            ],
        ],
        'is_active' => true,
        'due_at' => now()->subMinute(),
    ]);

    $this->artisan('schedule:run');

    $schedule->refresh();

    expect(ScheduleRunTestFailingInvokable::$invocationCount)->toBe(1)
        ->and($schedule->last_run)->not->toBeNull()
        ->and($schedule->last_success)->toBeNull()
        ->and($schedule->due_at->greaterThan(now()))->toBeTrue();
});
