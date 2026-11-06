<?php

use FluxErp\Jobs\SendTaskRemindersJob;
use FluxErp\Models\Task;
use FluxErp\Models\User;
use FluxErp\Notifications\Task\TaskReminderNotification;
use Illuminate\Support\Facades\Notification;

test('due reminder notifies the subscribed responsible user', function (): void {
    Notification::fake();

    $responsibleUser = User::factory()->create();

    $task = Task::factory()->create([
        'responsible_user_id' => $responsibleUser->getKey(),
        'state' => 'open',
        'due_date' => now()->addMinutes(5)->toDateString(),
        'due_time' => now()->addMinutes(5)->format('H:i:s'),
        'has_due_reminder' => true,
        'due_reminder_minutes_before' => 10,
    ]);

    $responsibleUser->subscribeNotificationChannel($task->broadcastChannel(), '*');

    (new SendTaskRemindersJob())->handle();

    expect($task->refresh()->due_reminder_sent_at)->not->toBeNull();
    Notification::assertSentTo($responsibleUser, TaskReminderNotification::class);
});
