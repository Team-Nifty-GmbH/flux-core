<?php

use FluxErp\Jobs\CreateDocumentsJob;
use Illuminate\Support\Facades\Storage;

test('deduplicates preview filenames in zip', function (): void {
    Storage::fake();

    $job = app(CreateDocumentsJob::class, [
        'items' => [],
        'selectedPrintLayouts' => ['preview' => ['invoice']],
        'userMorph' => 'user:' . $this->user->getKey(),
    ]);

    $previewOutputs = [];
    for ($i = 1; $i <= 3; $i++) {
        $previewOutputs[] = [
            'output' => 'PDF content ' . $i,
            'file_name' => 'Rechnung Vorschau.pdf',
        ];
    }

    $reflection = new ReflectionMethod($job, 'storeFiles');
    $filePath = $reflection->invoke($job, $previewOutputs, []);

    $zipContent = Storage::get($filePath);
    $tmpZip = tempnam(sys_get_temp_dir(), 'test-') . '.zip';
    file_put_contents($tmpZip, $zipContent);

    $zip = new ZipArchive();
    $zip->open($tmpZip);

    expect($zip->numFiles)->toBe(3)
        ->and($zip->getNameIndex(0))->toBe('Rechnung Vorschau.pdf')
        ->and($zip->getNameIndex(1))->toBe('Rechnung Vorschau (1).pdf')
        ->and($zip->getNameIndex(2))->toBe('Rechnung Vorschau (2).pdf')
        ->and($zip->getFromIndex(0))->toBe('PDF content 1')
        ->and($zip->getFromIndex(1))->toBe('PDF content 2')
        ->and($zip->getFromIndex(2))->toBe('PDF content 3');

    $zip->close();
    unlink($tmpZip);
});

test('keeps unique filenames unchanged in zip', function (): void {
    Storage::fake();

    $job = app(CreateDocumentsJob::class, [
        'items' => [],
        'selectedPrintLayouts' => ['preview' => ['invoice']],
        'userMorph' => 'user:' . $this->user->getKey(),
    ]);

    $previewOutputs = [
        ['output' => 'PDF A', 'file_name' => 'Rechnung RE-001.pdf'],
        ['output' => 'PDF B', 'file_name' => 'Rechnung RE-002.pdf'],
        ['output' => 'PDF C', 'file_name' => 'Rechnung RE-003.pdf'],
    ];

    $reflection = new ReflectionMethod($job, 'storeFiles');
    $filePath = $reflection->invoke($job, $previewOutputs, []);

    $zipContent = Storage::get($filePath);
    $tmpZip = tempnam(sys_get_temp_dir(), 'test-') . '.zip';
    file_put_contents($tmpZip, $zipContent);

    $zip = new ZipArchive();
    $zip->open($tmpZip);

    expect($zip->numFiles)->toBe(3)
        ->and($zip->getNameIndex(0))->toBe('Rechnung RE-001.pdf')
        ->and($zip->getNameIndex(1))->toBe('Rechnung RE-002.pdf')
        ->and($zip->getNameIndex(2))->toBe('Rechnung RE-003.pdf');

    $zip->close();
    unlink($tmpZip);
});
