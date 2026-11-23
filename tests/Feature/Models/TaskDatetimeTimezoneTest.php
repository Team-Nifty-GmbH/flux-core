<?php

use FluxErp\Events\Task\TaskReminderEvent;
use FluxErp\Jobs\SendTaskRemindersJob;
use FluxErp\Models\Task;
use FluxErp\States\Task\Open;
use Illuminate\Support\Facades\Event;

beforeEach(function (): void {
    config(['app.timezone' => 'UTC', 'flux.display_timezone' => null]);
    $this->user->update(['timezone' => 'Europe/Berlin']);
});

test('due datetime converts the wall time of the user to utc', function (string $date, string $expected): void {
    $task = Task::factory()->create([
        'due_date' => $date,
        'due_time' => '09:00:00',
    ]);

    expect($task->refresh()->getRawOriginal('due_datetime'))->toBe($expected);
})->with([
    'winter time' => ['2026-01-15', '2026-01-15 08:00:00'],
    'summer time' => ['2026-07-15', '2026-07-15 07:00:00'],
]);

test('start datetime converts the wall time of the user to utc', function (string $date, string $expected): void {
    $task = Task::factory()->create([
        'start_date' => $date,
        'start_time' => '09:00:00',
    ]);

    expect($task->refresh()->getRawOriginal('start_datetime'))->toBe($expected);
})->with([
    'winter time' => ['2026-01-15', '2026-01-15 08:00:00'],
    'summer time' => ['2026-07-15', '2026-07-15 07:00:00'],
]);

test('due date without time ends at midnight of the user', function (): void {
    $task = Task::factory()->create([
        'due_date' => '2026-07-15',
        'due_time' => null,
    ]);

    expect($task->refresh()->getRawOriginal('due_datetime'))->toBe('2026-07-15 21:59:59');
});

test('display timezone is used when the user has none', function (): void {
    config(['flux.display_timezone' => 'Europe/Berlin']);
    $this->user->update(['timezone' => null]);

    $task = Task::factory()->create([
        'due_date' => '2026-01-15',
        'due_time' => '09:00:00',
    ]);

    expect($task->refresh()->getRawOriginal('due_datetime'))->toBe('2026-01-15 08:00:00');
});

test('due reminder is sent on time for a berlin wall time', function (): void {
    Event::fake([TaskReminderEvent::class]);

    $due = now('Europe/Berlin')->addMinutes(30)->startOfMinute();

    $task = Task::factory()->create([
        'state' => Open::class,
        'start_date' => null,
        'due_date' => $due->toDateString(),
        'due_time' => $due->toTimeString(),
        'has_due_reminder' => true,
        'due_reminder_minutes_before' => 45,
    ]);

    SendTaskRemindersJob::dispatchSync();

    expect($task->refresh()->due_reminder_sent_at)->not->toBeNull();
    Event::assertDispatched(TaskReminderEvent::class);
});
