<?php

use FluxErp\Models\JobBatch;
use FluxErp\Models\QueueMonitor;
use FluxErp\Models\User;
use FluxErp\Notifications\QueueMonitor\Batch\BatchFinishedNotification;
use FluxErp\Notifications\QueueMonitor\Job\JobFinishedNotification;

beforeEach(function (): void {
    $this->notifiable = User::factory()->create();
});

test('a job that finishes twice writes one notification instead of failing', function (): void {
    $monitor = QueueMonitor::factory()->create([
        'job_id' => 'job-that-finishes-twice',
        'job_batch_id' => null,
        'message' => 'first run',
    ]);

    $this->notifiable->notify(new JobFinishedNotification($monitor));

    $monitor->update(['message' => 'second run']);

    $this->notifiable->notify(new JobFinishedNotification($monitor->refresh()));

    expect($this->notifiable->notifications()->count())->toBe(1)
        ->and(data_get($this->notifiable->notifications()->first()->data, 'description'))
        ->toBe('second run');
});

test('a batch that finishes twice writes one notification instead of failing', function (): void {
    JobBatch::query()->insert([
        'id' => 'batch-that-finishes-twice',
        'name' => 'Export',
        'total_jobs' => 1,
        'pending_jobs' => 0,
        'failed_jobs' => 1,
        'failed_job_ids' => json_encode([]),
        'created_at' => now()->timestamp,
        'finished_at' => now()->timestamp,
    ]);

    $batch = JobBatch::query()->findOrFail('batch-that-finishes-twice');

    $this->notifiable->notify(new BatchFinishedNotification($batch));
    $this->notifiable->notify(new BatchFinishedNotification($batch->refresh()));

    expect($this->notifiable->notifications()->count())->toBe(1);
});

test('two different jobs still write their own notification', function (): void {
    $first = QueueMonitor::factory()->create(['job_id' => 'first-job', 'job_batch_id' => null]);
    $second = QueueMonitor::factory()->create(['job_id' => 'second-job', 'job_batch_id' => null]);

    $this->notifiable->notify(new JobFinishedNotification($first));
    $this->notifiable->notify(new JobFinishedNotification($second));

    expect($this->notifiable->notifications()->count())->toBe(2);
});

test('the same finished job reaches a second recipient with its own notification', function (): void {
    $second = User::factory()->create();

    $monitor = QueueMonitor::factory()->create([
        'job_id' => 'job-with-two-recipients',
        'job_batch_id' => null,
        'message' => 'done',
    ]);

    $this->notifiable->notify(new JobFinishedNotification($monitor));
    $second->notify(new JobFinishedNotification($monitor));

    expect($this->notifiable->notifications()->count())->toBe(1)
        ->and($second->notifications()->count())->toBe(1)
        ->and($this->notifiable->notifications()->first()->getKey())
        ->not->toBe($second->notifications()->first()->getKey());
});

test('both recipients keep the job derived context id, so the toast stays addressable', function (): void {
    $second = User::factory()->create();

    $monitor = QueueMonitor::factory()->create([
        'job_id' => 'job-sharing-one-toast',
        'job_batch_id' => null,
    ]);

    $this->notifiable->notify(new JobFinishedNotification($monitor));
    $second->notify(new JobFinishedNotification($monitor));

    $expected = (new JobFinishedNotification($monitor))->id;

    expect(data_get($this->notifiable->notifications()->first()->data, 'contextId'))->toBe($expected)
        ->and(data_get($second->notifications()->first()->data, 'contextId'))->toBe($expected);
});

test('a second recipient still collapses a repeat onto the row it already has', function (): void {
    $second = User::factory()->create();

    $monitor = QueueMonitor::factory()->create([
        'job_id' => 'job-repeating-for-two',
        'job_batch_id' => null,
        'message' => 'first run',
    ]);

    $this->notifiable->notify(new JobFinishedNotification($monitor));
    $second->notify(new JobFinishedNotification($monitor));

    $monitor->update(['message' => 'second run']);

    $this->notifiable->notify(new JobFinishedNotification($monitor->refresh()));
    $second->notify(new JobFinishedNotification($monitor->refresh()));

    expect($this->notifiable->notifications()->count())->toBe(1)
        ->and($second->notifications()->count())->toBe(1)
        ->and(data_get($second->notifications()->first()->data, 'description'))->toBe('second run');
});
