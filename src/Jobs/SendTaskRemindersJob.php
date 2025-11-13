<?php

namespace FluxErp\Jobs;

use Cron\CronExpression;
use FluxErp\Console\Scheduling\Repeatable;
use FluxErp\Events\Task\TaskReminderEvent;
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
        return 'Send reminders for tasks that are due or starting soon.';
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
        $this->sendStartReminders();
        $this->sendDueReminders();
    }

    protected function sendStartReminders(): void
    {
        resolve_static(Task::class, 'query')
            ->where('has_start_reminder', true)
            ->whereNotNull('start_datetime')
            ->whereNull('start_reminder_sent_at')
            ->whereNotIn('state', [Done::class, Canceled::class])
            ->whereRaw('start_datetime > NOW()')
            ->whereRaw('TIMESTAMPDIFF(MINUTE, NOW(), start_datetime) <= COALESCE(start_reminder_minutes_before, 0)')
            ->with(['responsibleUser', 'users'])
            ->each(function (Task $task): void {
                event(app(TaskReminderEvent::class, ['task' => $task, 'type' => 'start']));

                $task->start_reminder_sent_at = now();
                $task->saveQuietly();
            });
    }

    protected function sendDueReminders(): void
    {
        resolve_static(Task::class, 'query')
            ->where('has_due_reminder', true)
            ->whereNotNull('due_datetime')
            ->whereNull('due_reminder_sent_at')
            ->whereNotIn('state', [Done::class, Canceled::class])
            ->whereRaw('due_datetime > NOW()')
            ->whereRaw('TIMESTAMPDIFF(MINUTE, NOW(), due_datetime) <= COALESCE(due_reminder_minutes_before, 0)')
            ->with(['responsibleUser', 'users'])
            ->each(function (Task $task): void {
                event(app(TaskReminderEvent::class, ['task' => $task, 'type' => 'due']));

                $task->due_reminder_sent_at = now();
                $task->saveQuietly();
            });
    }
}
