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
