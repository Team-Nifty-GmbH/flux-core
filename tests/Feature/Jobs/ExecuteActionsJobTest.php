<?php

use FluxErp\Actions\Task\DeleteTask;
use FluxErp\Actions\Task\UpdateTask;
use FluxErp\Jobs\ExecuteActionsJob;
use FluxErp\Models\Task;
use Illuminate\Support\Str;

test('runs a flux action for scalar id payloads', function (): void {
    $tasks = Task::factory()
        ->count(3)
        ->create(['name' => Str::uuid(), 'state' => 'open']);

    (new ExecuteActionsJob(DeleteTask::class, $tasks->pluck('id')->toArray()))->handle();

    expect(Task::query()->whereKey($tasks->pluck('id'))->exists())->toBeFalse();
});

test('runs a flux action for full data array payloads', function (): void {
    $tasks = Task::factory()
        ->count(2)
        ->create(['name' => Str::uuid(), 'state' => 'open']);

    $payloads = $tasks
        ->map(fn (Task $task): array => [
            'id' => $task->getKey(),
            'state' => 'done',
            'start_date' => $task->start_date?->toDateString(),
            'due_date' => $task->due_date?->toDateString(),
        ])
        ->all();

    (new ExecuteActionsJob(UpdateTask::class, $payloads))->handle();

    $tasks->each(
        fn (Task $task) => $this->assertDatabaseHas('tasks', [
            'id' => $task->getKey(),
            'state' => 'done',
        ])
    );
});

test('does nothing for a class that is not a flux action', function (): void {
    $task = Task::factory()->create(['name' => Str::uuid(), 'state' => 'open']);

    (new ExecuteActionsJob(Task::class, [$task->getKey()]))->handle();

    expect(Task::query()->whereKey($task->getKey())->exists())->toBeTrue();
});

test('handles empty payloads without error', function (): void {
    (new ExecuteActionsJob(DeleteTask::class, []))->handle();
})->throwsNoExceptions();
