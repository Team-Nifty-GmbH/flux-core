<?php

use Cron\CronExpression;
use FluxErp\Console\Scheduling\Repeatable;
use FluxErp\Enums\RepeatableTypeEnum;
use FluxErp\Facades\Repeatable as RepeatableFacade;
use FluxErp\Models\Schedule;
use FluxErp\Traits\Job\TracksSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;

class ScheduleRunTestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, TracksSchedule;

    public function handle(): void
    {
        throw new RuntimeException('Job failed');
    }
}

class ScheduleRunTestSuccessJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, TracksSchedule;

    public function handle(): void {}
}

class ScheduleRunTestInvokable
{
    public static int $invocationCount = 0;

    public function __invoke(): void
    {
        static::$invocationCount++;
    }
}

class ScheduleRunTestDefaultCronInvokable implements Repeatable
{
    public static bool $wasInvoked = false;

    public function __invoke(): void
    {
        static::$wasInvoked = true;
    }

    public static function defaultCron(): ?CronExpression
    {
        return new CronExpression('* * * * *');
    }

    public static function description(): ?string
    {
        return 'Test default cron invokable';
    }

    public static function isRepeatable(): bool
    {
        return true;
    }

    public static function name(): string
    {
        return 'DefaultCronInvokable';
    }

    public static function parameters(): array
    {
        return [];
    }

    public static function withoutOverlapping(): bool
    {
        return false;
    }
}

it('does not set last_success when a scheduled job fails', function (): void {
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

it('sets last_success when a scheduled job completes successfully', function (): void {
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

it('increments current_recurrence when a scheduled job with recurrences completes', function (): void {
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

it('sets due_at to last day of next month for lastDayOfMonth schedules', function (): void {
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

it('does not double fire overdue schedule when cron matches later the same day', function (): void {
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

it('runs a repeatable with defaultCron without a db entry', function (): void {
    ScheduleRunTestDefaultCronInvokable::$wasInvoked = false;

    RepeatableFacade::register(
        ScheduleRunTestDefaultCronInvokable::name(),
        ScheduleRunTestDefaultCronInvokable::class
    );

    $this->travelTo(Carbon::create(2025, 6, 15, 10, 0, 0));

    $this->artisan('schedule:run');

    expect(ScheduleRunTestDefaultCronInvokable::$wasInvoked)->toBeTrue();
});

it('uses db entry over defaultCron when both exist', function (): void {
    ScheduleRunTestDefaultCronInvokable::$wasInvoked = false;

    RepeatableFacade::register(
        ScheduleRunTestDefaultCronInvokable::name(),
        ScheduleRunTestDefaultCronInvokable::class
    );

    $this->travelTo(Carbon::create(2025, 6, 15, 10, 0, 0));

    // Create a DB entry for the same class with a yearly cron (won't be due now)
    $schedule = resolve_static(Schedule::class, 'query')->create([
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
