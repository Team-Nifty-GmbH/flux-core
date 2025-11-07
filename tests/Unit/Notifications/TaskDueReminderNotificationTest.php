<?php

use FluxErp\Events\Task\TaskDueReminderEvent;
use FluxErp\Models\Task;
use FluxErp\Models\User;
use FluxErp\Notifications\Task\TaskDueReminderNotification;
use Illuminate\Support\Facades\Notification;
use function Livewire\invade;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->responsibleUser = User::factory()->create();
    $this->assignedUser = User::factory()->create();

    $this->task = Task::factory()->create([
        'name' => 'Test Task',
        'responsible_user_id' => $this->responsibleUser->id,
        'due_date' => now()->addDay(),
        'due_time' => '14:00:00',
    ]);

    $this->task->users()->attach($this->assignedUser->id);
});

test('notification has correct title', function (): void {
    $this->actingAs($this->user);

    $notification = new TaskDueReminderNotification();
    $event = app(TaskDueReminderEvent::class, ['task' => $this->task]);
    $notification->event = $event;
    $notification->model = $this->task;

    $title = invade($notification)->getTitle();

    expect($title)->toBe(__('Task :name is due soon', ['name' => 'Test Task']));
});

test('notification has correct icon', function (): void {
    $notification = new TaskDueReminderNotification();
    $event = app(TaskDueReminderEvent::class, ['task' => $this->task]);
    $notification->event = $event;
    $notification->model = $this->task;

    $icon = invade($notification)->getNotificationIcon();

    expect($icon)->toBe('clock');
});

test('notification has description with datetime', function (): void {
    $this->actingAs($this->user);

    $notification = new TaskDueReminderNotification();
    $event = app(TaskDueReminderEvent::class, ['task' => $this->task]);
    $notification->event = $event;
    $notification->model = $this->task;

    $description = invade($notification)->getDescription();

    expect($description)->toBeString()->not->toBeEmpty();
});

test('event includes responsible user and assigned users as subscribers', function (): void {
    $event = app(TaskDueReminderEvent::class, ['task' => $this->task]);

    $subscribers = $event->getSubscribers();

    expect($subscribers)
        ->toHaveCount(2)
        ->toContain($this->responsibleUser)
        ->toContain($this->assignedUser);
});

test('event returns collection when task has no users', function (): void {
    $taskWithoutUsers = Task::factory()->create([
        'responsible_user_id' => null,
        'due_date' => now()->addDay(),
    ]);

    $event = app(TaskDueReminderEvent::class, ['task' => $taskWithoutUsers]);

    $subscribers = $event->getSubscribers();

    expect($subscribers)->toBeInstanceOf(Illuminate\Support\Collection::class)->toBeEmpty();
});

test('notification can be sent via event', function (): void {
    Notification::fake();

    $this->actingAs($this->user);

    $event = app(TaskDueReminderEvent::class, ['task' => $this->task]);
    $notification = new TaskDueReminderNotification();
    $notification->sendNotification($event);

    Notification::assertSentTo(
        [$this->responsibleUser, $this->assignedUser],
        TaskDueReminderNotification::class
    );
});
