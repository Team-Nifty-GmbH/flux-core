<?php

use FluxErp\Models\QueueMonitor;
use FluxErp\Models\User;
use FluxErp\Notifications\QueueMonitor\Job\JobFinishedNotification;
use FluxErp\Notifications\QueueMonitor\Job\JobProcessingNotification;
use FluxErp\Notifications\QueueMonitor\Job\JobStartedNotification;
use FluxErp\Support\Notification\ToastNotification\NotificationAction;

beforeEach(function (): void {
    $this->notifiable = User::factory()->create();

    $this->action = NotificationAction::make()
        ->label('Download')
        ->url('https://example.test/storage/exports/test.xlsx')
        ->download();
});

test('JobProcessingNotification carries the accept action stored on the monitor', function (): void {
    $monitor = QueueMonitor::factory()->create([
        'job_id' => 'job-with-accept',
        'job_batch_id' => null,
        'accept' => serialize($this->action),
    ]);

    $payload = (new JobProcessingNotification($monitor))->toArray($this->notifiable);

    expect(data_get($payload, 'accept.label'))->toBe('Download');
    expect(data_get($payload, 'accept.url'))->toBe('https://example.test/storage/exports/test.xlsx');
    expect(data_get($payload, 'accept.download'))->toBeTrue();
});

test('JobStartedNotification carries the accept action stored on the monitor', function (): void {
    $monitor = QueueMonitor::factory()->create([
        'job_id' => 'started-with-accept',
        'job_batch_id' => null,
        'accept' => serialize($this->action),
    ]);

    $payload = (new JobStartedNotification($monitor))->toArray($this->notifiable);

    expect(data_get($payload, 'accept.label'))->toBe('Download');
    expect(data_get($payload, 'accept.url'))->toBe('https://example.test/storage/exports/test.xlsx');
});

test('JobProcessingNotification has no accept when the monitor has no accept', function (): void {
    $monitor = QueueMonitor::factory()->create([
        'job_id' => 'job-without-accept',
        'job_batch_id' => null,
        'accept' => null,
    ]);

    $payload = (new JobProcessingNotification($monitor))->toArray($this->notifiable);

    expect(data_get($payload, 'accept'))->toBeNull();
});

test('JobFinishedNotification still carries the accept action stored on the monitor', function (): void {
    $monitor = QueueMonitor::factory()->create([
        'job_id' => 'finished-with-accept',
        'job_batch_id' => null,
        'accept' => serialize($this->action),
    ]);

    $payload = (new JobFinishedNotification($monitor))->toArray($this->notifiable);

    expect(data_get($payload, 'accept.label'))->toBe('Download');
    expect(data_get($payload, 'accept.url'))->toBe('https://example.test/storage/exports/test.xlsx');
});
