<?php

namespace FluxErp\Jobs;

use FluxErp\Actions\Printing;
use FluxErp\Actions\PrintJob\CreatePrintJob;
use FluxErp\Contracts\OffersPrinting;
use FluxErp\Models\Media;
use FluxErp\Notifications\DocumentsReady;
use FluxErp\View\Printing\PrintableView;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Throwable;
use ZipArchive;

class CreateDocumentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        protected array $items,
        protected array $selectedPrintLayouts,
        protected string $userMorph,
        protected ?int $printerId = null,
        protected ?string $printerSize = null,
        protected int $printerQuantity = 1,
    ) {}

    public function handle(): void
    {
        $downloadFiles = [];
        $previewFiles = [];
        $printMediaIds = [];

        foreach ($this->items as $itemData) {
            $modelClass = morphed_model($itemData['model_type']);
            if (! $modelClass) {
                continue;
            }

            $item = resolve_static($modelClass, 'query')
                ->whereKey($itemData['model_id'])
                ->first();

            if (! $item instanceof OffersPrinting) {
                continue;
            }

            $printViews = $item->resolvePrintViews();

            foreach (data_get($itemData, 'layouts') ?? [] as $layout) {
                if (! array_key_exists($layout, $printViews)) {
                    continue;
                }

                $isForce = in_array($layout, data_get($this->selectedPrintLayouts, 'force', []));
                $isDownload = in_array($layout, data_get($this->selectedPrintLayouts, 'download', []));
                $isPrint = in_array($layout, data_get($this->selectedPrintLayouts, 'print', []));
                $isPreview = in_array($layout, data_get($this->selectedPrintLayouts, 'preview', []));

                if ($isPreview) {
                    try {
                        /** @var PrintableView $file */
                        $file = Printing::make([
                            'model_type' => $item->getMorphClass(),
                            'model_id' => $item->getKey(),
                            'view' => $layout,
                            'preview' => true,
                        ])
                            ->validate()
                            ->execute();

                        $previewFiles[] = [
                            'output' => $file->pdf->output(),
                            'file_name' => Str::finish($file->getFileName(), '.pdf'),
                        ];
                    } catch (Throwable $e) {
                        report($e);
                    }

                    if (! $isDownload && ! $isPrint && ! $isForce) {
                        continue;
                    }
                }

                $media = $item instanceof HasMedia ? $item->getMedia($layout)->last() : null;

                if (! $media || $isForce) {
                    try {
                        /** @var PrintableView $file */
                        $file = Printing::make([
                            'model_type' => $item->getMorphClass(),
                            'model_id' => $item->getKey(),
                            'view' => $layout,
                        ])
                            ->validate()
                            ->execute();

                        if ($file->shouldStore()) {
                            $media = $file->attachToModel($item);
                        }
                    } catch (Throwable $e) {
                        report($e);

                        continue;
                    }
                }

                if (! $media) {
                    continue;
                }

                if ($isDownload && $media->getKey()) {
                    $downloadFiles[] = $media;
                }

                if ($isPrint && $media->getKey()) {
                    $printMediaIds[] = $media->getKey();
                }
            }
        }

        if ($printMediaIds && $this->printerId) {
            foreach ($printMediaIds as $mediaId) {
                try {
                    CreatePrintJob::make([
                        'media_id' => $mediaId,
                        'printer_id' => $this->printerId,
                        'size' => $this->printerSize,
                        'quantity' => $this->printerQuantity,
                    ])
                        ->validate()
                        ->execute();
                } catch (Throwable $e) {
                    report($e);
                }
            }
        }

        $user = morph_to($this->userMorph);
        if (! $user) {
            return;
        }

        $filePath = null;

        if ($previewFiles) {
            $filePath = $this->storePreviewDownloads($previewFiles);
        } elseif ($downloadFiles) {
            $filePath = $this->storeDownloads($downloadFiles);
        }

        $totalCount = count($previewFiles) ?: count($downloadFiles) ?: count($this->items);

        $user->notify(DocumentsReady::make($totalCount, $filePath));
    }

    /**
     * @param  array<array{output: string, file_name: string}>  $files
     */
    protected function storePreviewDownloads(array $files): string
    {
        $folder = 'documents/' . str_replace(':', '_', $this->userMorph) . '/';
        $disk = Storage::disk();

        if (count($files) === 1) {
            $filePath = $folder . $files[0]['file_name'];
            $disk->put($filePath, $files[0]['output']);

            return $filePath;
        }

        $zipName = __('Preview') . '_' . now()->toDateString() . '.zip';
        $zipPath = $folder . str_replace(['<', '>', ':', '"', '/', '\\', '|', '?', '*'], '_', $zipName);
        $tmpZip = tempnam(sys_get_temp_dir(), 'flux-preview-') . '.zip';

        $zip = new ZipArchive();
        $zip->open($tmpZip, ZipArchive::CREATE);

        foreach ($files as $file) {
            $zip->addFromString($file['file_name'], $file['output']);
        }

        $zip->close();

        $disk->put($zipPath, file_get_contents($tmpZip));
        unlink($tmpZip);

        return $zipPath;
    }

    /**
     * @param  Media[]  $files
     */
    protected function storeDownloads(array $files): string
    {
        $folder = 'documents/' . str_replace(':', '_', $this->userMorph) . '/';
        $disk = Storage::disk();

        if (count($files) === 1) {
            $media = $files[0];
            $fileName = Str::finish($media->file_name, '.pdf');
            $filePath = $folder . $fileName;

            $disk->put($filePath, file_get_contents($media->getPath()));

            return $filePath;
        }

        $zipName = __('Documents') . '_' . now()->toDateString() . '.zip';
        $zipPath = $folder . str_replace(['<', '>', ':', '"', '/', '\\', '|', '?', '*'], '_', $zipName);
        $tmpZip = tempnam(sys_get_temp_dir(), 'flux-docs-') . '.zip';

        $zip = new ZipArchive();
        $zip->open($tmpZip, ZipArchive::CREATE);

        foreach ($files as $media) {
            $zip->addFile($media->getPath(), $media->file_name);
        }

        $zip->close();

        $disk->put($zipPath, file_get_contents($tmpZip));
        unlink($tmpZip);

        return $zipPath;
    }
}
