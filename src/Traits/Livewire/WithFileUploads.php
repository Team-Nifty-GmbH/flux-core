<?php

namespace FluxErp\Traits\Livewire;

use FluxErp\Models\Media;
use FluxErp\Services\MediaService;
use Illuminate\Support\Facades\Storage;
use Livewire\TemporaryUploadedFile;
use Livewire\WithFileUploads as WithFileUploadsBase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

trait WithFileUploads
{
    use WithFileUploadsBase {
        WithFileUploadsBase::removeUpload as parentRemoveUpload;
        WithFileUploadsBase::finishUpload as parentFinishUpload;
    }

    public array $filesArray = [];

    public string $collection = '';

    public ?string $subFolder = null;

    public function finishUpload($name, $tmpPath, $isMultiple): void
    {
        if (! $isMultiple) {
            $this->parentFinishUpload($name, $tmpPath, $isMultiple);

            return;
        }

        $this->cleanupOldUploads();

        $files = collect($tmpPath)->map(function ($i) {
            return TemporaryUploadedFile::createFromLivewire($i);
        })->toArray();
        $this->emitSelf('upload:finished', $name, collect($files)->map->getFilename()->toArray());

        $files = array_merge($this->getPropertyValue($name), $files);

        $this->syncInput($name, $files);

        $this->prepareForMediaLibrary($name);

        $this->skipRender();
    }

    public function download(Media $mediaItem): BinaryFileResponse
    {
        return response()->download(Storage::disk('local')->path($mediaItem->getPath()), $mediaItem->name);
    }

    public function removeUpload(string $name, int $index): void
    {
        $this->parentRemoveUpload($name, $this->filesArray[$index]['key']);
        unset($this->filesArray[$index]);

        $this->skipRender();
    }

    public function prepareForMediaLibrary(string $name, ?int $modelId = null, ?string $modelType = null): void
    {
        $this->filesArray = [];
        $property = $this->getPropertyValue($name);
        $property = ! is_array($property) ? [$property] : $property;

        $modelId = $modelId ?: $this->modelId ?? null;
        $modelType = $modelType ?: $this->modelType;

        $collection = trim(
            str_replace('/', '.', $this->collection . $this->subFolder), '.'
        ) ?: 'default';

        foreach ($property as $file) {
            $this->filesArray[] = [
                'key' => $file->getFilename(),
                'name' => $file->getClientOriginalName(),
                'model_id' => $modelId,
                'model_type' => $modelType,
                'collection_name' => $collection,
                'media' => $file->getRealPath(),
            ];
        }
    }

    public function saveFileUploadsToMediaLibrary(string $name, ?int $modelId = null, ?string $modelType = null): array
    {
        $mediaService = new MediaService();
        $response = [];

        $this->prepareForMediaLibrary($name, $modelId, $modelType);

        foreach ($this->filesArray as $file) {
            $response[] = $mediaService->upload($file);
        }

        $this->filesArray = [];
        $this->reset($name, 'subFolder');

        $this->cleanupOldUploads();

        return $response;
    }

    public function updatedSubFolder($value): void
    {
        $value = str_replace([' ', '.', '\\'], ['_', '/', '/'], trim($value));

        $this->subFolder = '/' . ltrim(preg_replace('/[^A-Za-z0-9_\/]/', '', $value), '/');

        $this->skipRender();
    }
}
