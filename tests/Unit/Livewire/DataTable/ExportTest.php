<?php

namespace FluxErp\Tests\Unit\Livewire\DataTable;

use FluxErp\Jobs\ExportDataTableJob;
use FluxErp\Livewire\DataTables\BaseDataTable;
use FluxErp\Models\Client;
use FluxErp\Notifications\ExportReady;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use function Livewire\invade;

class ExportTest extends BaseSetup
{
    public function test_can_export_data(): void
    {
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
                $this->assertSame(
                    route('private-storage', ['path' => $invaded->filePath]),
                    $downloadUrl = invade($toast->accept)->url
                );

                $this->get($downloadUrl)
                    ->assertStatus(200)
                    ->assertDownload(pathinfo($invaded->filePath, PATHINFO_BASENAME));

                return true;
            }
        );
    }
}

class ClientDataTableTest extends BaseDataTable
{
    public array $enabledCols = [
        'name',
    ];

    protected string $model = Client::class;
}
