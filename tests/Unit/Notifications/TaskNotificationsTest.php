<?php

use FluxErp\Events\Task\TaskAssignedEvent;
use FluxErp\Models\Task;
use FluxErp\Notifications\Task\TaskAssignedNotification;
use FluxErp\Notifications\Task\TaskCreatedNotification;
use FluxErp\Notifications\Task\TaskUpdatedNotification;
use function Livewire\invade;

beforeEach(function (): void {
    $this->task = Task::factory()->create([
        'name' => 'Notification Test Task',
    ]);
});

test('task assigned notification has title', function (): void {
    $notification = new TaskAssignedNotification();
    $event = app(TaskAssignedEvent::class, ['task' => $this->task]);
    $notification->event = $event;
    $notification->model = $this->task;

    $title = invade($notification)->getTitle();

    expect($title)->toBeString()->not->toBeEmpty();
});

test('task assigned notification has description', function (): void {
    $notification = new TaskAssignedNotification();
    $event = app(TaskAssignedEvent::class, ['task' => $this->task]);
    $notification->event = $event;
    $notification->model = $this->task;

    $description = invade($notification)->getDescription();

    expect($description)->toBeString();
});

test('task created notification has correct icon', function (): void {
    $notification = new TaskCreatedNotification();
    $notification->model = $this->task;

    $icon = invade($notification)->getNotificationIcon();

    expect($icon)->toBeString()->not->toBeEmpty();
});

test('task updated notification has correct icon', function (): void {
    $notification = new TaskUpdatedNotification();
    $notification->model = $this->task;

    $icon = invade($notification)->getNotificationIcon();

    expect($icon)->toBeString()->not->toBeEmpty();
});
