<?php

use FluxErp\Actions\Task\CreateTask;
use FluxErp\Actions\Task\DeleteTask;
use FluxErp\Actions\Task\UpdateTask;
use FluxErp\Models\Task;

test('create task', function (): void {
    $task = CreateTask::make([
        'name' => 'Implement feature X',
    ])->validate()->execute();

    expect($task)
        ->toBeInstanceOf(Task::class)
        ->name->toBe('Implement feature X');
});

test('create task with dates', function (): void {
    $task = CreateTask::make([
        'name' => 'Deadline task',
        'start_date' => '2026-04-01',
        'due_date' => '2026-04-30',
    ])->validate()->execute();

    expect($task)
        ->start_date->not->toBeNull()
        ->due_date->not->toBeNull();
});

test('create task due_date must be after start_date', function (): void {
    CreateTask::assertValidationErrors([
        'name' => 'Invalid dates',
        'start_date' => '2026-04-30',
        'due_date' => '2026-04-01',
    ], 'due_date');
});

test('create task with user assignment', function (): void {
    $task = CreateTask::make([
        'name' => 'Assigned task',
        'users' => [$this->user->getKey()],
    ])->validate()->execute();

    expect($task->users)->toHaveCount(1);
});

test('update task', function (): void {
    $task = Task::factory()->create();

    $updated = UpdateTask::make([
        'id' => $task->getKey(),
        'name' => 'Updated task name',
        'start_date' => $task->start_date,
        'due_date' => $task->due_date,
    ])->validate()->execute();

    expect($updated->name)->toBe('Updated task name');
});

test('delete task', function (): void {
    $task = Task::factory()->create();

    $result = DeleteTask::make(['id' => $task->getKey()])
        ->validate()->execute();

    expect($result)->toBeTrue();
});

test('update task without dates keeps dates and reminders', function (): void {
    $task = Task::factory()->create([
        'start_date' => '2026-11-02',
        'start_time' => '09:30:00',
        'has_start_reminder' => true,
        'start_reminder_minutes_before' => 30,
        'due_date' => '2026-11-06',
        'due_time' => '16:00:00',
        'has_due_reminder' => true,
        'due_reminder_minutes_before' => 60,
    ]);

    UpdateTask::make([
        'id' => $task->getKey(),
        'name' => 'Renamed',
    ])
        ->validate()
        ->execute();

    expect($task->refresh())
        ->name->toBe('Renamed')
        ->start_date->toDateString()->toBe('2026-11-02')
        ->start_time->toBe('09:30:00')
        ->has_start_reminder->toBeTrue()
        ->start_reminder_minutes_before->toBe(30)
        ->due_date->toDateString()->toBe('2026-11-06')
        ->due_time->toBe('16:00:00')
        ->has_due_reminder->toBeTrue()
        ->due_reminder_minutes_before->toBe(60);
});

test('update task dates without times keeps the times', function (): void {
    $task = Task::factory()->create([
        'start_date' => '2026-11-02',
        'start_time' => '09:30:00',
        'due_date' => '2026-11-06',
        'due_time' => '16:00:00',
    ]);

    UpdateTask::make([
        'id' => $task->getKey(),
        'start_date' => '2026-11-03',
        'due_date' => '2026-11-09',
    ])
        ->validate()
        ->execute();

    expect($task->refresh())
        ->start_date->toDateString()->toBe('2026-11-03')
        ->start_time->toBe('09:30:00')
        ->due_date->toDateString()->toBe('2026-11-09')
        ->due_time->toBe('16:00:00');
});

test('update task clearing a date turns its reminder off', function (): void {
    $task = Task::factory()->create([
        'start_date' => '2026-11-02',
        'has_start_reminder' => true,
        'start_reminder_minutes_before' => 30,
        'due_date' => '2026-11-06',
    ]);

    UpdateTask::make([
        'id' => $task->getKey(),
        'start_date' => null,
    ])
        ->validate()
        ->execute();

    expect($task->refresh())
        ->start_date->toBeNull()
        ->has_start_reminder->toBeFalse()
        ->due_date->toDateString()->toBe('2026-11-06');
});

test('update task due date must not be before the stored start date', function (): void {
    $task = Task::factory()->create([
        'start_date' => '2026-11-10',
        'due_date' => '2026-11-12',
    ]);

    UpdateTask::assertValidationErrors([
        'id' => $task->getKey(),
        'due_date' => '2026-11-01',
    ], 'due_date');
});
