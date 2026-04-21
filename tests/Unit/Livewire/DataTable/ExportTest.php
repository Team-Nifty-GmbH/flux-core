<?php

use FluxErp\Jobs\ExportDataTableJob;
use FluxErp\Models\Tenant;
use FluxErp\Notifications\ExportReady;
use FluxErp\Tests\Unit\Livewire\DataTable\ExportTestDataTable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use function Livewire\invade;

test('can export data', function (): void {
    Queue::fake([ExportDataTableJob::class]);
    Notification::fake();
    Storage::fake(config('filesystems.default'));

    Livewire::test(ExportTestDataTable::class)
        ->assertOk()
        ->call('export')
        ->assertToastNotification(type: 'success');

    Queue::assertPushed(ExportDataTableJob::class);
    Queue::assertCount(1);

    /** @var ExportDataTableJob $job */
    $job = data_get(Queue::pushedJobs(), ExportDataTableJob::class . '.0.job');
    $job->handle();

    Notification::assertSentTo(
        $this->user,
        ExportReady::class,
        function (ExportReady $notification) {
            $invaded = invade($notification);
            Storage::disk(config('filesystems.default'))->assertExists($invaded->filePath);
            $toast = invade($invaded->toToastNotification($this->user));
            expect($downloadUrl = invade($toast->accept)->url)->toBe(route('private-storage', ['path' => $invaded->filePath]));

            $this->get($downloadUrl)
                ->assertOk()
                ->assertDownload(pathinfo($invaded->filePath, PATHINFO_BASENAME));

            return true;
        }
    );
});

test('export logs activity', function (): void {
    Storage::fake(config('filesystems.default'));

    $job = new ExportDataTableJob(
        serialize(Livewire::test(ExportTestDataTable::class)->instance()),
        Tenant::class,
        [],
        $this->user->getMorphClass() . ':' . $this->user->getKey()
    );

    $job->handle();

    $activity = Activity::query()->where('event', 'export_started')->latest()->first();

    expect($activity)
        ->not->toBeNull()
        ->causer_id->toBe($this->user->getKey())
        ->log_name->toBe('export')
        ->and($activity->properties->toArray())->toMatchArray([
            'model' => Tenant::class,
            'columns' => [],
        ]);
});
