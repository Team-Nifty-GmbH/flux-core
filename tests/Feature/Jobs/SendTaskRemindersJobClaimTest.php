<?php

use FluxErp\Events\Task\TaskReminderEvent;
use FluxErp\Jobs\SendTaskRemindersJob;
use FluxErp\Models\Task;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

function changeTaskRowOnFirstRetrieve(array $attributes): void
{
    $changed = false;

    Task::retrieved(function (Task $task) use (&$changed, $attributes): void {
        if ($changed) {
            return;
        }

        $changed = true;

        DB::table('tasks')
            ->where('id', $task->getKey())
            ->update($attributes);
    });
}

beforeEach(function (): void {
    Event::fake([TaskReminderEvent::class]);

    $this->dueTask = Task::factory()->create([
        'state' => 'open',
        'due_date' => now()->addMinutes(5)->toDateString(),
        'due_time' => now()->addMinutes(5)->format('H:i:s'),
        'has_due_reminder' => true,
        'due_reminder_minutes_before' => 10,
    ]);
});

test('due reminder is sent once and marked', function (): void {
    (new SendTaskRemindersJob())->handle();

    Event::assertDispatchedTimes(TaskReminderEvent::class, 1);
    expect($this->dueTask->refresh()->due_reminder_sent_at)->not->toBeNull();
});

test('claiming a reminder leaves the modification of the task untouched', function (): void {
    $before = DB::table('tasks')
        ->where('id', $this->dueTask->getKey())
        ->first(['updated_at', 'updated_by']);

    $this->travel(1)->minutes();

    (new SendTaskRemindersJob())->handle();

    $after = DB::table('tasks')
        ->where('id', $this->dueTask->getKey())
        ->first(['due_reminder_sent_at', 'updated_at', 'updated_by']);

    expect($after->due_reminder_sent_at)->not->toBeNull()
        ->and($after->updated_at)->toBe($before->updated_at)
        ->and($after->updated_by)->toBe($before->updated_by);
});

test('due reminder claimed by another run is not sent again', function (): void {
    changeTaskRowOnFirstRetrieve(['due_reminder_sent_at' => now()]);

    (new SendTaskRemindersJob())->handle();

    Event::assertNotDispatched(TaskReminderEvent::class);
});

test('due reminder of a moved due date stays pending', function (): void {
    changeTaskRowOnFirstRetrieve([
        'due_date' => now()->addDays(2)->toDateString(),
        'due_datetime' => now()->addDays(2),
        'due_reminder_sent_at' => null,
    ]);

    (new SendTaskRemindersJob())->handle();

    Event::assertNotDispatched(TaskReminderEvent::class);
    expect($this->dueTask->refresh()->due_reminder_sent_at)->toBeNull();
});

test('start reminder claimed by another run is not sent again', function (): void {
    $this->dueTask->update(['has_due_reminder' => false]);

    Task::factory()->create([
        'state' => 'open',
        'start_date' => now()->addMinutes(5)->toDateString(),
        'start_time' => now()->addMinutes(5)->format('H:i:s'),
        'has_start_reminder' => true,
        'start_reminder_minutes_before' => 10,
    ]);

    changeTaskRowOnFirstRetrieve(['start_reminder_sent_at' => now()]);

    (new SendTaskRemindersJob())->handle();

    Event::assertNotDispatched(TaskReminderEvent::class);
});
