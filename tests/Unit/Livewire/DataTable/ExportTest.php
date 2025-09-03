<?php

namespace FluxErp\Tests\Unit\Livewire\DataTable;

uses(\FluxErp\Tests\Livewire\BaseSetup::class);
use FluxErp\Jobs\ExportDataTableJob;
use FluxErp\Livewire\DataTables\BaseDataTable;
use FluxErp\Notifications\ExportReady;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use function Livewire\invade;

test('can export data', function (): void {
    Queue::fake([ExportDataTableJob::class]);
    Notification::fake();
    Storage::fake(config('filesystems.default'));

    Livewire::test(ClientDataTableTest::class)
        ->assertStatus(200)
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
                ->assertStatus(200)
                ->assertDownload(pathinfo($invaded->filePath, PATHINFO_BASENAME));

            return true;
        }
    );
});

class ClientDataTableTest extends BaseDataTable
{
    protected string $model = \FluxErp\Models\Client::class;
}
