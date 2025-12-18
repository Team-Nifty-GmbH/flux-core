<?php

namespace FluxErp\Jobs;

use Cron\CronExpression;
use FluxErp\Console\Scheduling\Repeatable;
use FluxErp\Events\Task\TaskReminderEvent;
use FluxErp\Models\Task;
use FluxErp\States\Task\TaskState;
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
        $endStates = TaskState::all()
            ->filter(fn ($state) => $state::$isEndState)
            ->keys()
            ->toArray();

        $this->sendStartReminders($endStates);
        $this->sendDueReminders($endStates);
    }

    protected function sendStartReminders(array $endStates): void
    {
        resolve_static(Task::class, 'query')
            ->whereNull('start_reminder_sent_at')
            ->whereNotNull('start_datetime')
            ->whereRaw('start_datetime > NOW()')
            ->whereNotIn('state', $endStates)
            ->where('has_start_reminder', true)
            ->whereRaw('TIMESTAMPDIFF(MINUTE, NOW(), start_datetime) <= COALESCE(start_reminder_minutes_before, 0)')
            ->with(['responsibleUser', 'users'])
            ->each(function (Task $task): void {
                event(app(TaskReminderEvent::class, ['task' => $task, 'type' => 'start']));

                $task->start_reminder_sent_at = now();
                $task->saveQuietly();
            });
    }

    protected function sendDueReminders(array $endStates): void
    {
        resolve_static(Task::class, 'query')
            ->whereNull('due_reminder_sent_at')
            ->whereNotNull('due_datetime')
            ->whereRaw('due_datetime > NOW()')
            ->whereNotIn('state', $endStates)
            ->where('has_due_reminder', true)
            ->whereRaw('TIMESTAMPDIFF(MINUTE, NOW(), due_datetime) <= COALESCE(due_reminder_minutes_before, 0)')
            ->with(['responsibleUser', 'users'])
            ->each(function (Task $task): void {
                event(app(TaskReminderEvent::class, ['task' => $task, 'type' => 'due']));

                $task->due_reminder_sent_at = now();
                $task->saveQuietly();
            });
    }
}
