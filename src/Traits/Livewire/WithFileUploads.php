<?php

namespace FluxErp\Traits\Livewire;

use FluxErp\Actions\Media\DownloadMedia;
use FluxErp\Actions\Media\DownloadMultipleMedia;
use FluxErp\Actions\Media\UploadMedia;
use FluxErp\Models\Media;
use FluxErp\Models\MediaFolder;
use FluxErp\Support\MediaLibrary\FluxMediaStream;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads as WithFileUploadsBase;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

    public function downloadCollection(int|string $id, array|string $collection): ?FluxMediaStream
    {
        // If $id is an integer we assume it's a media folder
        if (is_int($id)) {
            $mediaFolder = resolve_static(MediaFolder::class, 'familyTree')
                ->whereKey($id)
                ->first();

            $actionData = [
                'file_name' => trim($mediaFolder->name),
                'media_folders' => [$mediaFolder?->getKey()],
            ];
        } else {
            $collection = is_array($collection) ? implode('.', $collection) : $collection;

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
            return DownloadMultipleMedia::make($actionData)
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);
        }

        return null;
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

    public function removeFileUpload(string $name, int $index): void
    {
        unset($this->filesArray[$index]);

        $this->skipRender();
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
