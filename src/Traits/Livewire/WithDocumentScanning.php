<?php

namespace FluxErp\Traits\Livewire;

use FluxErp\Jobs\ProcessScannedDocumentJob;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Renderless;

trait WithDocumentScanning
{
    use EnsureUsedInLivewire;

    abstract protected function getScannedDocumentAction(): string;

    #[Renderless]
    public function submitScan(string $imageData): bool
    {
        if (! preg_match(
            '#^data:image/(jpeg|png|gif|webp);base64,#',
            $imageData
        )) {
            return false;
        }

        $base64Part = substr($imageData, strpos($imageData, ',') + 1);
        $decoded = base64_decode($base64Part, true);

        if ($decoded === false || ! getimagesizefromstring($decoded)) {
            return false;
        }

        $storagePath = 'scans/' . Str::uuid() . '.img';
        Storage::put($storagePath, $decoded);

        dispatch(app(ProcessScannedDocumentJob::class, [
            'imagePath' => $storagePath,
            'action' => $this->getScannedDocumentAction(),
        ]));

        return true;
    }

    #[Renderless]
    public function notifyScanResults(int $successCount, int $errorCount): void
    {
        if ($successCount > 0) {
            $this->toast()
                ->success(__(':count document(s) queued for processing.', ['count' => $successCount]))
                ->send();
        }

        if ($errorCount > 0) {
            $this->toast()
                ->error(__(':count document(s) could not be uploaded.', ['count' => $errorCount]))
                ->send();
        }
    }
}
