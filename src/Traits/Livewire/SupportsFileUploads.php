<?php

namespace FluxErp\Traits\Livewire;

use FluxErp\Actions\Media\UploadMedia;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads as WithFileUploadsBase;

trait SupportsFileUploads
{
    use EnsureUsedInLivewire, WithFileUploadsBase;

    public string $collection = '';

    public array $filesArray = [];

    public bool $filesArrayDirty = false;

    public function prepareForMediaLibrary(string $name, ?int $modelId = null, ?string $modelType = null): void
    {
        $this->filesArrayDirty = true;
        $files = Arr::wrap($this->getPropertyValue($name));

        $modelId = $modelId ?: $this->modelId ?? null;
        $modelType = $modelType ?: $this->modelType;

        $collection = $this->collection ?: 'default';

        $keys = array_column($this->filesArray, 'key');
        foreach ($files as $file) {
            if (! in_array($file->getFilename(), $keys)) {
                /** @var TemporaryUploadedFile $file */
                $this->filesArray[] = [
                    'key' => $file->getFilename(),
                    'name' => $file->getClientOriginalName(),
                    'file_name' => $file->getClientOriginalName(),
                    'model_id' => $modelId,
                    'model_type' => $modelType,
                    'collection_name' => $collection,
                    'media' => $file->getRealPath(),
                ];
            }
        }
    }

    public function removeFileUpload(string $name, int $index): void
    {
        unset($this->filesArray[$index]);

        $this->skipRender();
    }

    public function saveFileUploadsToMediaLibrary(string $name, ?int $modelId = null, ?string $modelType = null): array
    {
        $files = Arr::wrap($this->getPropertyValue($name));
        $filenames = array_map(fn (TemporaryUploadedFile $file) => $file->getFilename(), $files);

        $this->filesArray = array_filter(
            $this->filesArray,
            fn (array $media) => in_array(data_get($media, 'key'), $filenames)
        );

        if ($this->filesArray && $this->filesArrayDirty) {
            $this->filesArray = array_map(
                fn ($file) => array_merge($file, ['model_type' => $modelType, 'model_id' => $modelId]),
                $this->filesArray
            );
        }

        if (count($this->filesArray) !== count($files)) {
            $this->prepareForMediaLibrary($name, $modelId, $modelType);
        }

        $response = [];
        DB::transaction(
            function () use (&$response): void {
                foreach ($this->filesArray as $file) {
                    $response[] = UploadMedia::make($file)
                        ->checkPermission()
                        ->validate()
                        ->execute()
                        ->toArray();
                }
            },
            5
        );

        $this->filesArray = [];
        $this->filesArrayDirty = false;

        $this->cleanupOldUploads();

        return $response;
    }
}
