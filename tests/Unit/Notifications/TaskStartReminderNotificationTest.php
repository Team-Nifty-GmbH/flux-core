<?php

use FluxErp\Events\Task\TaskStartReminderEvent;
use FluxErp\Models\Language;
use FluxErp\Models\Task;
use FluxErp\Models\User;
use FluxErp\Notifications\Task\TaskStartReminderNotification;
use Illuminate\Support\Facades\Notification;
use function Livewire\invade;

beforeEach(function (): void {
    $language = Language::factory()->create();

    $this->user = User::factory()->create(['language_id' => $language->id]);
    $this->responsibleUser = User::factory()->create(['language_id' => $language->id]);
    $this->assignedUser = User::factory()->create(['language_id' => $language->id]);

    $this->task = Task::factory()->create([
        'name' => 'Test Task',
        'responsible_user_id' => $this->responsibleUser->id,
        'start_date' => now()->addDay(),
        'start_time' => '09:00:00',
    ]);

    $this->task->users()->attach($this->assignedUser->id);
});

test('notification has correct title', function (): void {
    $this->actingAs($this->user);

    $notification = new TaskStartReminderNotification();
    $event = app(TaskStartReminderEvent::class, ['task' => $this->task]);
    $notification->event = $event;
    $notification->model = $this->task;

    $title = invade($notification)->getTitle();

    expect($title)->toBe(__('Task :name is starting soon', ['name' => 'Test Task']));
});

test('notification has correct icon', function (): void {
    $notification = new TaskStartReminderNotification();
    $event = app(TaskStartReminderEvent::class, ['task' => $this->task]);
    $notification->event = $event;
    $notification->model = $this->task;

    $icon = invade($notification)->getNotificationIcon();

    expect($icon)->toBe('play');
});

test('notification has description with datetime', function (): void {
    $this->actingAs($this->user);

    $notification = new TaskStartReminderNotification();
    $event = app(TaskStartReminderEvent::class, ['task' => $this->task]);
    $notification->event = $event;
    $notification->model = $this->task;

    $description = invade($notification)->getDescription();

    expect($description)->toBeString()->not->toBeEmpty();
});

test('event includes responsible user and assigned users as subscribers', function (): void {
    $event = app(TaskStartReminderEvent::class, ['task' => $this->task]);

    $subscribers = $event->getSubscribers();

    expect($subscribers)
        ->toHaveCount(2)
        ->toContain($this->responsibleUser)
        ->toContain($this->assignedUser);
});

test('event returns collection when task has no users', function (): void {
    $taskWithoutUsers = Task::factory()->create([
        'responsible_user_id' => null,
        'start_date' => now()->addDay(),
    ]);

    $event = app(TaskStartReminderEvent::class, ['task' => $taskWithoutUsers]);

    $subscribers = $event->getSubscribers();

    expect($subscribers)->toBeInstanceOf(Illuminate\Support\Collection::class)->toBeEmpty();
});

test('notification can be sent via event', function (): void {
    Notification::fake();

    $this->actingAs($this->user);

    $event = app(TaskStartReminderEvent::class, ['task' => $this->task]);
    $notification = new TaskStartReminderNotification();
    $notification->sendNotification($event);

    Notification::assertSentTo(
        [$this->responsibleUser, $this->assignedUser],
        TaskStartReminderNotification::class
    );
});
