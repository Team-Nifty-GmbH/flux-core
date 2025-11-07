<?php

namespace FluxErp\Jobs;

use Cron\CronExpression;
use FluxErp\Console\Scheduling\Repeatable;
use FluxErp\Events\Task\TaskDueReminderEvent;
use FluxErp\Events\Task\TaskStartReminderEvent;
use FluxErp\Models\Task;
use FluxErp\States\Task\Canceled;
use FluxErp\States\Task\Done;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class SendTaskRemindersJob implements Repeatable, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct() {}

    public static function defaultCron(): ?CronExpression
    {
        return new CronExpression('* * * * *');
    }

    public static function description(): ?string
    {
        return __('Send reminders for tasks that are due or starting soon.');
    }

    public static function isRepeatable(): bool
    {
        return true;
    }

    public static function name(): string
    {
        return class_basename(self::class);
    }

    public static function parameters(): array
    {
        return [];
    }

    public function handle(): void
    {
        $this->sendDueReminders();
        $this->sendStartReminders();
    }

    protected function sendDueReminders(): void
    {
        resolve_static(Task::class, 'query')
            ->whereNotNull('due_datetime')
            ->whereBetween('due_datetime', [now()->addHours(23), now()->addHours(25)])
            ->whereNull('due_reminder_sent_at')
            ->whereNotIn('state', [Done::class, Canceled::class])
            ->with(['responsibleUser', 'users'])
            ->each(function (Task $task): void {
                event(app(TaskDueReminderEvent::class, ['task' => $task]));

                $task->due_reminder_sent_at = now();
                $task->saveQuietly();
            });
    }

    protected function sendStartReminders(): void
    {
        resolve_static(Task::class, 'query')
            ->whereNotNull('start_datetime')
            ->whereBetween('start_datetime', [now()->addHours(23), now()->addHours(25)])
            ->whereNull('start_reminder_sent_at')
            ->whereNotIn('state', [Done::class, Canceled::class])
            ->with(['responsibleUser', 'users'])
            ->each(function (Task $task): void {
                event(app(TaskStartReminderEvent::class, ['task' => $task]));

                $task->start_reminder_sent_at = now();
                $task->saveQuietly();
            });
    }
}
