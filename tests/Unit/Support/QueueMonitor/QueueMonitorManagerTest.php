<?php

use FluxErp\Jobs\ExportDataTableJob;
use FluxErp\Models\QueueMonitor;
use FluxErp\States\QueueMonitor\Running;
use FluxErp\Support\QueueMonitor\QueueMonitorManager;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobQueued;

beforeEach(function (): void {
    $this->makeExportJob = fn () => new ExportDataTableJob(
        component: serialize(new stdClass()),
        modelClass: 'FluxErp\\Models\\Tenant',
        columns: ['name'],
        userMorph: $this->user->getMorphClass() . ':' . $this->user->getKey(),
    );

    $this->makeQueueJob = function (string $jobId, string $queue, string $uuid) {
        $queueJob = Mockery::mock(Job::class);
        $queueJob->shouldReceive('resolveName')->andReturn(ExportDataTableJob::class);
        $queueJob->shouldReceive('getJobId')->andReturn($jobId);
        $queueJob->shouldReceive('getRawBody')->andReturn('');
        $queueJob->shouldReceive('getQueue')->andReturn($queue);
        $queueJob->shouldReceive('uuid')->andReturn($uuid);
        $queueJob->shouldReceive('attempts')->andReturn(1);
        $queueJob->shouldReceive('payload')->andReturn(['data' => ['commandName' => ExportDataTableJob::class]]);

        return $queueJob;
    };

    $this->originalRedisQueue = config('queue.connections.redis.queue');
});

afterEach(function (): void {
    config()->set('queue.connections.redis.queue', $this->originalRedisQueue);
});

test('jobQueued stores the resolved event queue, not the unset job property', function (): void {
    QueueMonitorManager::handle(new JobQueued(
        connectionName: 'redis',
        queue: 'flux_tenant_queue',
        id: 'job-id-1',
        payload: '',
        job: ($this->makeExportJob)(),
        delay: 0,
    ));

    expect(QueueMonitor::count())->toBe(1)
        ->and(QueueMonitor::first()->queue)->toBe('flux_tenant_queue');
});

test('jobQueued falls back to the connection default queue when the event queue is null', function (): void {
    config()->set('queue.connections.redis.queue', 'flux_tenant_queue');

    QueueMonitorManager::handle(new JobQueued(
        connectionName: 'redis',
        queue: null,
        id: 'job-id-null-queue',
        payload: '',
        job: ($this->makeExportJob)(),
        delay: 0,
    ));

    expect(QueueMonitor::count())->toBe(1)
        ->and(QueueMonitor::first()->queue)->toBe('flux_tenant_queue');
});

test('jobProcessing reuses the QueueMonitor created by a null-queue dispatch on the same connection', function (): void {
    config()->set('queue.connections.redis.queue', 'flux_tenant_queue');

    QueueMonitorManager::handle(new JobQueued(
        connectionName: 'redis',
        queue: null,
        id: 'job-id-1',
        payload: '',
        job: ($this->makeExportJob)(),
        delay: 0,
    ));

    expect(QueueMonitor::count())->toBe(1);

    QueueMonitorManager::handle(new JobProcessing(
        connectionName: 'redis',
        job: ($this->makeQueueJob)('job-id-1', 'flux_tenant_queue', 'uuid-1'),
    ));

    expect(QueueMonitor::count())->toBe(1)
        ->and(QueueMonitor::first()->state)->toBeInstanceOf(Running::class);
});
