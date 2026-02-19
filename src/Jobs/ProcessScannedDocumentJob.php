<?php

namespace FluxErp\Jobs;

use Barryvdh\DomPDF\Facade\Pdf;
use FluxErp\Actions\FluxAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Storage;

class ProcessScannedDocumentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * @param  class-string<FluxAction>  $action
     */
    public function __construct(
        protected readonly string $imagePath,
        protected readonly string $action
    ) {}

    public function handle(): void
    {
        $tempPath = null;

        try {
            $imageFilePath = Storage::path($this->imagePath);

            $tempPath = tempnam(sys_get_temp_dir(), 'scan_');
            $pdfPath = $tempPath . '.pdf';
            rename($tempPath, $pdfPath);
            $tempPath = $pdfPath;

            Pdf::loadView('flux::scan-to-pdf', ['imagePath' => $imageFilePath])
                ->setPaper('a4')
                ->save($tempPath);

            resolve_static($this->action, 'make', [['media' => $tempPath]])
                ->validate()
                ->execute();
        } finally {
            Storage::delete($this->imagePath);

            if ($tempPath && file_exists($tempPath)) {
                unlink($tempPath);
            }
        }
    }
}
