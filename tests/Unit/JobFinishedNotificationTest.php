<?php

use FluxErp\Models\QueueMonitor;
use FluxErp\Notifications\QueueMonitor\Job\JobFinishedNotification;

test('generates unique notification id for different queue monitors with same job id', function (): void {
    /** @var QueueMonitor $queueMonitor1 */
    $queueMonitor1 = QueueMonitor::factory()->create([
        'job_id' => 'test-job-123',
        'job_batch_id' => null,
    ]);

    /** @var QueueMonitor $queueMonitor2 */
    $queueMonitor2 = QueueMonitor::factory()->create([
        'job_id' => 'test-job-123',
        'job_batch_id' => null,
    ]);

    $notification1 = new JobFinishedNotification($queueMonitor1);
    $notification2 = new JobFinishedNotification($queueMonitor2);

    expect($notification1->id)->not->toBe($notification2->id);
});

test('generates unique notification id for different queue monitors with same batch id', function (): void {
    /** @var QueueMonitor $queueMonitor1 */
    $queueMonitor1 = QueueMonitor::factory()->create([
        'job_id' => 'test-job-1',
        'job_batch_id' => 'batch-123',
    ]);

    /** @var QueueMonitor $queueMonitor2 */
    $queueMonitor2 = QueueMonitor::factory()->create([
        'job_id' => 'test-job-2',
        'job_batch_id' => 'batch-123',
    ]);

    $notification1 = new JobFinishedNotification($queueMonitor1);
    $notification2 = new JobFinishedNotification($queueMonitor2);

    expect($notification1->id)->not->toBe($notification2->id);
});

test('generates consistent notification id for same queue monitor', function (): void {
    /** @var QueueMonitor $queueMonitor */
    $queueMonitor = QueueMonitor::factory()->create([
        'job_id' => 'test-job-456',
    ]);

    $notification1 = new JobFinishedNotification($queueMonitor);
    $notification2 = new JobFinishedNotification($queueMonitor);

    expect($notification1->id->toString())->toBe($notification2->id->toString());
});

test('notification id is a valid uuid', function (): void {
    /** @var QueueMonitor $queueMonitor */
    $queueMonitor = QueueMonitor::factory()->create([
        'job_id' => 'test-job-789',
    ]);

    $notification = new JobFinishedNotification($queueMonitor);

    expect($notification->id->toString())
        ->toBeString()
        ->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-5[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i');
});
