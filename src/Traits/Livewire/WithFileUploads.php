<?php

namespace FluxErp\Traits\Livewire;

use FluxErp\Actions\Media\UploadMedia;
use FluxErp\Models\Media;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads as WithFileUploadsBase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

trait WithFileUploads
{
    use EnsureUsedInLivewire, WithFileUploadsBase;

    public array $filesArray = [];

    public string $collection = '';

    public bool $filesArrayDirty = false;

    public function download(Media $mediaItem): false|BinaryFileResponse
    {
        if (! file_exists($mediaItem->getPath())) {
            if (method_exists($this, 'notification')) {
                $this->notification()->error(__('File not found!'))->send();
            }

            return false;
        }

        return response()->download($mediaItem->getPath(), $mediaItem->file_name);
    }

    public function downloadCollection(array|string $collection): ?BinaryFileResponse
    {
        $collection = is_array($collection) ? implode('.', $collection) : $collection;
        $media = resolve_static(Media::class, 'query')
            ->where('collection_name', 'like', $collection . '%')
            ->when($this->modelType ?? false,
                fn (Builder $query) => $query->where('model_type', morph_alias($this->modelType))
                    ->when(
                        $this->modelId ?? false,
                        fn ($query) => $query->where('model_id', $this->modelId)
                    )
            )
            ->get();

        // add files to a zip file
        $zip = new \ZipArchive();
        $zipFileName = explode('.', $collection);
        $zipFileName = array_pop($zipFileName);
        $zipFileName = $zipFileName . '.zip';

        $zip->open($zipFileName, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        foreach ($media as $file) {
            /** @var Media $file */
            if (! file_exists($file->getPath())) {
                continue;
            }

            if ($collection === $file->collection_name) {
                $relativePath = $file->name;
            } else {
                $collectionName = Str::remove($collection . '.', $file->collection_name);
                $relativePath = explode('.', $collectionName);
                $relativePath[] = $file->name;
                $relativePath = implode(DIRECTORY_SEPARATOR, $relativePath);
            }

            $zip->addFile($file->getPath(), $relativePath);
        }

        $count = $zip->count();
        $zip->close();

        if ($count) {
            // download zip file
            return response()->download($zipFileName)->deleteFileAfterSend();
        } else {
            return null;
        }
    }

    public function removeFileUpload(string $name, int $index): void
    {
        unset($this->filesArray[$index]);

        $this->skipRender();
    }

    public function prepareForMediaLibrary(string $name, ?int $modelId = null, ?string $modelType = null): void
    {
        $this->filesArrayDirty = true;
        $property = $this->getPropertyValue($name);
        $property = ! is_array($property) ? [$property] : $property;

        $modelId = $modelId ?: $this->modelId ?? null;
        $modelType = $modelType ?: $this->modelType;

        $collection = $this->collection ?: 'default';

        foreach ($property as $file) {
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

    public function saveFileUploadsToMediaLibrary(string $name, ?int $modelId = null, ?string $modelType = null): array
    {
        if (! $this->filesArray && ! $this->filesArrayDirty) {
            $this->prepareForMediaLibrary($name, $modelId, $modelType);
        } else {
            $this->filesArray = array_map(
                fn ($file) => array_merge($file, ['model_type' => $modelType, 'model_id' => $modelId]),
                $this->filesArray
            );
        }

        $response = [];
        foreach ($this->filesArray as $file) {
            $response[] = UploadMedia::make($file)
                ->checkPermission()
                ->validate()
                ->execute()
                ->toArray();
        }

        $this->filesArray = [];
        $this->filesArrayDirty = false;

        $this->cleanupOldUploads();

        return $response;
    }
}
