<?php

use FluxErp\Events\Task\TaskReminderEvent;
use FluxErp\Models\Language;
use FluxErp\Models\Task;
use FluxErp\Models\User;
use FluxErp\Notifications\Task\TaskReminderNotification;
use function Livewire\invade;

beforeEach(function (): void {
    $language = Language::factory()->create();

    $this->user = User::factory()->create(['language_id' => $language->id]);
    $this->responsibleUser = User::factory()->create(['language_id' => $language->id]);
    $this->assignedUser = User::factory()->create(['language_id' => $language->id]);

    $this->taskWithStart = Task::factory()->create([
        'name' => 'Test Task Start',
        'responsible_user_id' => $this->responsibleUser->id,
        'start_date' => now()->addDay(),
        'start_time' => '09:00:00',
    ]);

    $this->taskWithDue = Task::factory()->create([
        'name' => 'Test Task Due',
        'responsible_user_id' => $this->responsibleUser->id,
        'due_date' => now()->addDay(),
        'due_time' => '14:00:00',
    ]);

    $this->taskWithStart->users()->attach($this->assignedUser->id);
    $this->taskWithDue->users()->attach($this->assignedUser->id);
});

test('start reminder notification has correct title', function (): void {
    $this->actingAs($this->user);

    $notification = new TaskReminderNotification();
    $event = app(TaskReminderEvent::class, ['task' => $this->taskWithStart, 'type' => 'start']);
    $notification->event = $event;
    $notification->model = $this->taskWithStart;

    $title = invade($notification)->getTitle();

    expect($title)->toContain(__('is starting soon'))->toContain('Test Task Start');
});

test('due reminder notification has correct title', function (): void {
    $this->actingAs($this->user);

    $notification = new TaskReminderNotification();
    $event = app(TaskReminderEvent::class, ['task' => $this->taskWithDue, 'type' => 'due']);
    $notification->event = $event;
    $notification->model = $this->taskWithDue;

    $title = invade($notification)->getTitle();

    expect($title)->toContain(__('is due soon'))->toContain('Test Task Due');
});

test('start reminder notification has correct icon', function (): void {
    $notification = new TaskReminderNotification();
    $event = app(TaskReminderEvent::class, ['task' => $this->taskWithStart, 'type' => 'start']);
    $notification->event = $event;
    $notification->model = $this->taskWithStart;

    $icon = invade($notification)->getNotificationIcon();

    expect($icon)->toBe('play');
});

test('due reminder notification has correct icon', function (): void {
    $notification = new TaskReminderNotification();
    $event = app(TaskReminderEvent::class, ['task' => $this->taskWithDue, 'type' => 'due']);
    $notification->event = $event;
    $notification->model = $this->taskWithDue;

    $icon = invade($notification)->getNotificationIcon();

    expect($icon)->toBe('clock');
});

test('notification has description with datetime', function (): void {
    $this->actingAs($this->user);

    $notification = new TaskReminderNotification();
    $event = app(TaskReminderEvent::class, ['task' => $this->taskWithStart, 'type' => 'start']);
    $notification->event = $event;
    $notification->model = $this->taskWithStart;

    $description = invade($notification)->getDescription();

    expect($description)->toBeString()->not->toBeEmpty();
});

test('event includes responsible user and assigned users as subscribers', function (): void {
    $event = app(TaskReminderEvent::class, ['task' => $this->taskWithStart, 'type' => 'start']);

    $subscribers = $event->getSubscribers();
    $subscriberIds = $subscribers->pluck('id')->toArray();

    expect($subscribers)
        ->toHaveCount(2)
        ->and($subscriberIds)
        ->toContain($this->responsibleUser->id)
        ->toContain($this->assignedUser->id);
});

test('event returns collection when task has no users', function (): void {
    $taskWithoutUsers = Task::factory()->create([
        'responsible_user_id' => null,
        'start_date' => now()->addDay(),
    ]);

    $event = app(TaskReminderEvent::class, ['task' => $taskWithoutUsers, 'type' => 'start']);

    $subscribers = $event->getSubscribers();

    expect($subscribers)->toBeInstanceOf(Illuminate\Support\Collection::class)->toBeEmpty();
});

test('notification type is correct for start reminder', function (): void {
    $this->actingAs($this->user);

    $event = app(TaskReminderEvent::class, ['task' => $this->taskWithStart, 'type' => 'start']);

    expect($event->type)->toBe('start');
});

test('notification type is correct for due reminder', function (): void {
    $this->actingAs($this->user);

    $event = app(TaskReminderEvent::class, ['task' => $this->taskWithDue, 'type' => 'due']);

    expect($event->type)->toBe('due');
});
