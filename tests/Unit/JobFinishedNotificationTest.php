<?php

use FluxErp\Models\QueueMonitor;
use FluxErp\Notifications\QueueMonitor\Job\JobFinishedNotification;
use FluxErp\Support\Notification\ToastNotification\NotificationAction;

test('generates same notification id for different queue monitors with same job id', function (): void {
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

    expect($notification1->id)->toBe($notification2->id);
});

test('generates same notification id for different queue monitors with same batch id', function (): void {
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

    expect($notification1->id)->toBe($notification2->id);
});

test('generates unique notification id for different queue monitors with different batch id', function (): void {
    /** @var QueueMonitor $queueMonitor1 */
    $queueMonitor1 = QueueMonitor::factory()->create([
        'job_id' => 'test-job-1',
        'job_batch_id' => 'batch-123',
    ]);

    /** @var QueueMonitor $queueMonitor2 */
    $queueMonitor2 = QueueMonitor::factory()->create([
        'job_id' => 'test-job-1',
        'job_batch_id' => 'batch-124',
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

    expect($notification1->id)->toBe($notification2->id);
});

test('notification id is a valid uuid', function (): void {
    /** @var QueueMonitor $queueMonitor */
    $queueMonitor = QueueMonitor::factory()->create([
        'job_id' => 'test-job-789',
    ]);

    $notification = new JobFinishedNotification($queueMonitor);

    expect($notification->id)
        ->toBeString()
        ->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-5[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i');
});

test('finished toast without action auto-expires after 30 seconds by default', function (): void {
    $monitor = QueueMonitor::factory()->create([
        'accept' => serialize(false),
        'reject' => serialize(false),
        'data' => [],
    ]);

    $toast = (new JobFinishedNotification($monitor))->toToastNotification($this->user)->toArray();

    expect($toast['timeout'])->toBe(30)
        ->and($toast['persistent'] ?? null)->not->toBeTrue();
});

test('finished toast with action stays persistent by default', function (): void {
    $monitor = QueueMonitor::factory()->create([
        'accept' => serialize(NotificationAction::make()->label('Download')),
        'reject' => serialize(false),
        'data' => [],
    ]);

    $toast = (new JobFinishedNotification($monitor))->toToastNotification($this->user)->toArray();

    expect($toast['persistent'])->toBeTrue();
});

test('finished toast without action can be forced persistent', function (): void {
    $monitor = QueueMonitor::factory()->create([
        'accept' => serialize(false),
        'reject' => serialize(false),
        'data' => ['toast_persistent' => true],
    ]);

    $toast = (new JobFinishedNotification($monitor))->toToastNotification($this->user)->toArray();

    expect($toast['persistent'])->toBeTrue();
});

test('finished toast with action can be forced to time out', function (): void {
    $monitor = QueueMonitor::factory()->create([
        'accept' => serialize(NotificationAction::make()->label('Download')),
        'reject' => serialize(false),
        'data' => ['toast_timeout' => 10],
    ]);

    $toast = (new JobFinishedNotification($monitor))->toToastNotification($this->user)->toArray();

    expect($toast['timeout'])->toBe(10)
        ->and($toast['persistent'] ?? null)->not->toBeTrue();
});
