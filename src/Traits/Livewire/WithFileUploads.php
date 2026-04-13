<?php

namespace FluxErp\Traits\Livewire;

use FluxErp\Actions\Media\DownloadMedia;
use FluxErp\Actions\Media\DownloadMultipleMedia;
use FluxErp\Actions\Media\UploadMedia;
use FluxErp\Models\Media;
use FluxErp\Models\MediaFolder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads as WithFileUploadsBase;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait WithFileUploads
{
    use EnsureUsedInLivewire, WithFileUploadsBase;

    public string $collection = '';

    public array $filesArray = [];

    public bool $filesArrayDirty = false;

    public function download(Media $media): ?BinaryFileResponse
    {
        try {
            return DownloadMedia::make([
                'id' => $media->getKey(),
            ])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);
        }

        return null;
    }

    public function downloadCollection(int|string $id, array|string $collection): ?StreamedResponse
    {
        // If $id is an integer we assume it's a media folder
        if (is_int($id)) {
            $mediaFolder = resolve_static(MediaFolder::class, 'familyTree')
                ->whereKey($id)
                ->first();

            if (! $mediaFolder) {
                return null;
            }

            $fileName = trim($mediaFolder->name);
            $actionData = [
                'file_name' => $fileName,
                'media_folders' => [$mediaFolder->getKey()],
            ];
        } else {
            $collection = is_array($collection) ? implode('.', $collection) : $collection;
            $fileName = $collection;

            $actionData = [
                'media' => resolve_static(Media::class, 'query')
                    ->where('collection_name', 'like', $collection . '%')
                    ->when($this->modelType ?? false,
                        fn (Builder $query) => $query->where('model_type', morph_alias($this->modelType))
                            ->when(
                                $this->modelId ?? false,
                                fn ($query) => $query->where('model_id', $this->modelId)
                            )
                    )
                    ->pluck('id')
                    ->toArray(),
            ];
        }

        try {
            $stream = DownloadMultipleMedia::make($actionData)
                ->checkPermission()
                ->validate()
                ->execute();

            return response()->streamDownload(
                fn () => $stream->getZipStream(),
                str($fileName)->finish('.zip'),
                ['Content-Type' => 'application/zip']
            );
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);
        }

        return null;
    }

    public function prepareForMediaLibrary(string $name, ?int $modelId = null, ?string $modelType = null): void
    {
        $this->filesArrayDirty = true;
        $files = Arr::wrap($this->getPropertyValue($name));

        $modelId = $modelId ?: $this->modelId ?? null;
        $modelType = $modelType ?: $this->modelType;

        $collection = $this->collection ?: 'default';

        $keys = data_get($this->filesArray, '*.key') ?? [];
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
            function () use (&$response) {
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
