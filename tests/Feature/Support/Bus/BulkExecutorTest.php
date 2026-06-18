<?php

use FluxErp\Actions\Task\CreateTask;
use FluxErp\Actions\Task\DeleteTask;
use FluxErp\Actions\Task\UpdateTask;
use FluxErp\Models\Task;
use FluxErp\Support\Bus\BulkExecutor;
use Illuminate\Support\Str;

beforeEach(function (): void {
    config()->set('queue.default', 'sync');
});

test('throws for a flux action that is not bulk executable', function (): void {
    expect(fn () => BulkExecutor::make(CreateTask::class, [['name' => Str::uuid()]])->dispatch())
        ->toThrow(InvalidArgumentException::class);
});

test('throws for a class that is not a flux action', function (): void {
    expect(fn () => BulkExecutor::make(Task::class, [['id' => 1]])->dispatch())
        ->toThrow(InvalidArgumentException::class);
});

test('does nothing for empty payloads', function (): void {
    BulkExecutor::make(DeleteTask::class, [])->dispatch();
})->throwsNoExceptions();

test('runs the action for each payload', function (): void {
    $tasks = Task::factory()
        ->count(3)
        ->create(['name' => Str::uuid(), 'state' => 'open']);

    BulkExecutor::make(
        DeleteTask::class,
        $tasks->map(fn (Task $task): array => ['id' => $task->getKey()])->all(),
    )
        ->name('Deleting tasks')
        ->dispatch();

    expect(Task::query()->whereKey($tasks->pluck('id'))->exists())->toBeFalse();
});

test('runs an update action with full data payloads', function (): void {
    $tasks = Task::factory()
        ->count(2)
        ->create(['name' => Str::uuid(), 'state' => 'open']);

    BulkExecutor::make(
        UpdateTask::class,
        $tasks->map(fn (Task $task): array => [
            'id' => $task->getKey(),
            'state' => 'done',
            'start_date' => $task->start_date?->toDateString(),
            'due_date' => $task->due_date?->toDateString(),
        ])->all(),
    )->dispatch();

    $tasks->each(fn (Task $task) => $this->assertDatabaseHas('tasks', [
        'id' => $task->getKey(),
        'state' => 'done',
    ]));
});
