<?php

use FluxErp\Jobs\ExportDataTableJob;
use FluxErp\Models\Tenant;
use FluxErp\Notifications\ExportReady;
use FluxErp\Tests\Unit\Livewire\DataTable\ExportTestDataTable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Spatie\Activitylog\Models\Activity;
use function Livewire\invade;

function readJobOutput(ExportDataTableJob $job): array
{
    Notification::fake();
    $job->handle();

    $filePath = null;
    Notification::assertSentTo(
        test()->user,
        ExportReady::class,
        function (ExportReady $notification) use (&$filePath): bool {
            $filePath = invade($notification)->filePath;

            return true;
        }
    );

    return [
        'path' => $filePath,
        'contents' => Storage::disk(config('filesystems.default'))->get($filePath),
    ];
}

test('can export data with explicit columns', function (): void {
    Queue::fake([ExportDataTableJob::class]);
    Notification::fake();
    Storage::fake(config('filesystems.default'));

    app(Tenant::class)->create([
        'name' => 'Acme Inc',
        'tenant_code' => 'ACME',
        'is_active' => true,
    ]);

    Livewire::test(ExportTestDataTable::class)
        ->assertOk()
        ->call('export', ['name', 'tenant_code'])
        ->assertToastNotification(type: 'success');

    Queue::assertPushed(ExportDataTableJob::class);

    /** @var ExportDataTableJob $job */
    $job = data_get(Queue::pushedJobs(), ExportDataTableJob::class . '.0.job');
    $output = readJobOutput($job);

    expect($output['path'])->toEndWith('.xlsx');

    $tmp = tempnam(sys_get_temp_dir(), 'flux-export-test-');
    file_put_contents($tmp, $output['contents']);

    try {
        $rows = IOFactory::createReaderForFile($tmp)
            ->load($tmp)
            ->getActiveSheet()
            ->toArray();

        expect($rows[0])->toBe([__('Name'), __('Tenant Code')]);
        expect(collect($rows)->skip(1)->pluck(0)->all())->toContain('Acme Inc');
    } finally {
        @unlink($tmp);
    }
});

test('export with empty columns falls back to enabledCols', function (): void {
    Queue::fake([ExportDataTableJob::class]);
    Storage::fake(config('filesystems.default'));

    app(Tenant::class)->create([
        'name' => 'Globex Corp',
        'tenant_code' => 'GLOB',
        'is_active' => true,
    ]);

    Livewire::test(ExportTestDataTable::class)
        ->call('export', []);

    /** @var ExportDataTableJob $job */
    $job = data_get(Queue::pushedJobs(), ExportDataTableJob::class . '.0.job');
    $output = readJobOutput($job);

    $tmp = tempnam(sys_get_temp_dir(), 'flux-export-test-');
    file_put_contents($tmp, $output['contents']);

    try {
        $rows = IOFactory::createReaderForFile($tmp)
            ->load($tmp)
            ->getActiveSheet()
            ->toArray();

        expect($rows[0])->toBe([__('Name'), __('Tenant Code')]);
        expect(collect($rows)->skip(1)->pluck(0)->all())->toContain('Globex Corp');
    } finally {
        @unlink($tmp);
    }
});

test('csv format produces a csv file with semicolon-separated rows', function (): void {
    Queue::fake([ExportDataTableJob::class]);
    Storage::fake(config('filesystems.default'));

    app(Tenant::class)->create([
        'name' => 'Sirius Cybernetics',
        'tenant_code' => 'SC',
        'is_active' => true,
    ]);

    Livewire::test(ExportTestDataTable::class)
        ->call('export', ['name', 'tenant_code'], 'csv', true);

    /** @var ExportDataTableJob $job */
    $job = data_get(Queue::pushedJobs(), ExportDataTableJob::class . '.0.job');
    $output = readJobOutput($job);

    expect($output['path'])->toEndWith('.csv');

    $bom = "\xEF\xBB\xBF";
    expect($output['contents'])->toStartWith($bom);

    $body = substr($output['contents'], strlen($bom));
    $lines = explode("\n", trim($body));

    expect(str_getcsv($lines[0], ';'))->toBe([__('Name'), __('Tenant Code')]);
    expect(collect($lines)->skip(1)->map(fn (string $line): array => str_getcsv($line, ';'))->all())
        ->toContain(['Sirius Cybernetics', 'SC']);
});

test('json format produces a valid json file', function (): void {
    Queue::fake([ExportDataTableJob::class]);
    Storage::fake(config('filesystems.default'));

    app(Tenant::class)->create([
        'name' => 'Stark Industries',
        'tenant_code' => 'STARK',
        'is_active' => true,
    ]);

    Livewire::test(ExportTestDataTable::class)
        ->call('export', ['name', 'tenant_code'], 'json', true);

    /** @var ExportDataTableJob $job */
    $job = data_get(Queue::pushedJobs(), ExportDataTableJob::class . '.0.job');
    $output = readJobOutput($job);

    expect($output['path'])->toEndWith('.json');

    $decoded = json_decode($output['contents'], true);

    expect($decoded)->toBeArray();
    expect(collect($decoded)->pluck('name')->all())->toContain('Stark Industries');
});

test('export logs activity with format and formatted properties', function (): void {
    Storage::fake(config('filesystems.default'));

    $job = new ExportDataTableJob(
        serialize(Livewire::test(ExportTestDataTable::class)->instance()),
        Tenant::class,
        ['name'],
        $this->user->getMorphClass() . ':' . $this->user->getKey(),
        'json',
        false,
    );

    $job->handle();

    $activity = Activity::query()->where('event', 'export_started')->latest()->first();

    expect($activity)
        ->not->toBeNull()
        ->causer_id->toBe($this->user->getKey())
        ->log_name->toBe('export')
        ->and($activity->properties->toArray())->toMatchArray([
            'model' => Tenant::class,
            'columns' => ['name'],
            'format' => 'json',
            'formatted' => false,
        ]);
});
