<?php

use FluxErp\Jobs\ExportDataTableJob;
use FluxErp\Models\QueueMonitor;
use FluxErp\States\QueueMonitor\Running;
use FluxErp\Support\QueueMonitor\QueueMonitorManager;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobQueued;

test('jobQueued stores the resolved event queue, not the unset job property', function (): void {
    $job = new ExportDataTableJob(
        component: serialize(new stdClass()),
        modelClass: 'FluxErp\\Models\\Tenant',
        columns: ['name'],
        userMorph: $this->user->getMorphClass() . ':' . $this->user->getKey(),
    );

    QueueMonitorManager::handle(new JobQueued(
        connectionName: 'redis',
        queue: 'flux_tenant_queue',
        id: 'job-id-1',
        payload: '',
        job: $job,
        delay: 0,
    ));

    expect(QueueMonitor::count())->toBe(1);
    expect(QueueMonitor::first()->queue)->toBe('flux_tenant_queue');
});

test('jobQueued falls back to the connection default queue when the event queue is null', function (): void {
    config()->set('queue.connections.redis.queue', 'flux_tenant_queue');

    $job = new ExportDataTableJob(
        component: serialize(new stdClass()),
        modelClass: 'FluxErp\\Models\\Tenant',
        columns: ['name'],
        userMorph: $this->user->getMorphClass() . ':' . $this->user->getKey(),
    );

    QueueMonitorManager::handle(new JobQueued(
        connectionName: 'redis',
        queue: null,
        id: 'job-id-null-queue',
        payload: '',
        job: $job,
        delay: 0,
    ));

    expect(QueueMonitor::first()->queue)->toBe('flux_tenant_queue');
});

test('jobProcessing reuses the QueueMonitor created by a null-queue dispatch on the same connection', function (): void {
    config()->set('queue.connections.redis.queue', 'flux_tenant_queue');

    $userJob = new ExportDataTableJob(
        component: serialize(new stdClass()),
        modelClass: 'FluxErp\\Models\\Tenant',
        columns: ['name'],
        userMorph: $this->user->getMorphClass() . ':' . $this->user->getKey(),
    );

    QueueMonitorManager::handle(new JobQueued(
        connectionName: 'redis',
        queue: null,
        id: 'job-id-1',
        payload: '',
        job: $userJob,
        delay: 0,
    ));

    expect(QueueMonitor::count())->toBe(1);

    $queueJob = Mockery::mock(Job::class);
    $queueJob->shouldReceive('resolveName')->andReturn(ExportDataTableJob::class);
    $queueJob->shouldReceive('getJobId')->andReturn('job-id-1');
    $queueJob->shouldReceive('getRawBody')->andReturn('');
    $queueJob->shouldReceive('getQueue')->andReturn('flux_tenant_queue');
    $queueJob->shouldReceive('uuid')->andReturn('uuid-1');
    $queueJob->shouldReceive('attempts')->andReturn(1);
    $queueJob->shouldReceive('payload')->andReturn(['data' => ['commandName' => ExportDataTableJob::class]]);

    QueueMonitorManager::handle(new JobProcessing(
        connectionName: 'redis',
        job: $queueJob,
    ));

    expect(QueueMonitor::count())->toBe(1);
    expect(QueueMonitor::first()->state)->toBeInstanceOf(Running::class);
});
