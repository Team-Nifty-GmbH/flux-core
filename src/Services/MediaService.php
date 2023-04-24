<?php

namespace FluxErp\Services;

use Carbon\Carbon;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\Media;
use FluxErp\Models\Setting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaService
{
    /**
     * @throws \League\Flysystem\FilesystemException
     */
    public function downloadPublic(string $filename, array $data): array
    {
        $mediaItem = Media::query()
            ->where('model_type', $data['model_type'])
            ->where('model_id', $data['model_id'])
            ->where('file_name', $filename)
            ->first();

        if (! $mediaItem) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['media' => __('File not found (MediaItem empty)')]
            );
        }

        if (($data['conversion'] ?? false) && ! $mediaItem->hasGeneratedConversion($data['conversion'])) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['conversion' => __('File not found (conversion not generated)')]
            );
        }

        if ((config('filesystems.disks.' . $mediaItem->disk)['visibility'] ?? null) !== 'public') {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['public' => __('File not found (not public)')]
            );
        }

        return $this->download($mediaItem->id, $data);
    }

    /**
     * @throws \League\Flysystem\FilesystemException
     */
    public function download(string $id, array $data): array
    {
        $mediaItem = Media::query()
            ->whereKey($id)
            ->first();

        if (! $mediaItem) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['media' => __('File not found (MediaItem empty)')]
            );
        }

        if (($data['conversion'] ?? false) && ! $mediaItem->hasGeneratedConversion($data['conversion'])) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['conversion' => __('File not found (conversion not generated)')]
            );
        }

        $pathGenerator = app(config('media-library.path_generator'));
        $calculatedPath = $pathGenerator->getPath($mediaItem);
        $storage = Storage::disk($mediaItem->disk);

        if (! $storage->exists($calculatedPath . $mediaItem->file_name)) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['file' => __('Path not found')]
            );
        }

        return ResponseHelper::createArrayResponse(
            statusCode: 200,
            data: $mediaItem->withoutRelations(),
            additions: ['file_contents' => base64_encode($storage->read($calculatedPath . $mediaItem->file_name))]
        );
    }

    public function upload(array $data): array
    {
        $modelInstance = $data['model_type']::query()->whereKey($data['model_id'])->first();

        $customProperties = $this->customProperties($data, $data['model_type']);

        $file = $data['media'];
        $filename = $file instanceof UploadedFile ?
            $file->getClientOriginalName() : hash('sha512', microtime(false) . Str::uuid());
        $collectionName = $data['collection_name'] ?? 'default';
        $diskName = $data['disk'] ?? (
            $modelInstance->getRegisteredMediaCollections()
                ->where('name', $collectionName)
                ->first()
                ?->diskName
            ?: config('media-library.disk_name')
        );

        $data['name'] = $data['name'] ?? $filename;
        $data['collection_name'] = $collectionName;

        if ($this->validateFileExists($data)) {
            return ResponseHelper::createArrayResponse(
                statusCode: 409,
                data: ['filename' => 'file name already exists']
            );
        }

        if ($data['media_type'] ?? false) {
            $media = $modelInstance
                ->{'addMediaFrom' . $data['media_type']}($file)
                ->setName($data['name'])
                ->withCustomProperties($customProperties)
                ->storingConversionsOnDisk(config('flux.media.conversion'))
                ->toMediaCollection(collectionName: $collectionName, diskName: $diskName);
        } else {
            $media = $modelInstance
                ->addMedia($file instanceof UploadedFile ? $file->path() : $file)
                ->setName($data['name'])
                ->withCustomProperties($customProperties)
                ->storingConversionsOnDisk(config('flux.media.conversion'))
                ->toMediaCollection(collectionName: $collectionName, diskName: $diskName);
        }

        return ResponseHelper::createArrayResponse(
            statusCode: 201,
            data: $media->withoutRelations(),
            additions: ['url' => $media->getUrl()]
        );
    }

    public function replace(string $id, array $data): array
    {
        $mediaItem = Media::query()
            ->whereKey($id)
            ->first();

        if (! $mediaItem) {
            return ResponseHelper::createArrayResponse(statusCode: 404, data: ['media' => 'media not found']);
        }

        $customProperties = $this->customProperties($data, $mediaItem->model_type);
        $diskName = $data['disk'] ?? (
            $mediaItem->model->getRegisteredMediaCollections()
                ->where('name', $mediaItem->collection_name)
                ->first()
                ?->diskName
            ?: config('media-library.disk_name')
        );

        $file = $data['media'];
        $filename = $file instanceof UploadedFile ?
            $file->getClientOriginalName() : hash('sha512', microtime(false) . Str::uuid());

        $mediaItem->name = $data['name'] ?? $filename;

        if (
            $this->validateFileExists(
                $mediaItem->makeVisible(['model_type', 'model_id', 'collection_name', 'name'])
                    ->toArray()
            )
        ) {
            return ResponseHelper::createArrayResponse(
                statusCode: 409,
                data: ['filename' => 'file name already exists']
            );
        }

        $this->delete($id);

        if ($data['media_type'] ?? false) {
            $media = $mediaItem->model
                ->{'addMediaFrom' . $data['media_type']}($file)
                ->setName($data['name'] ?? $filename)
                ->withCustomProperties($customProperties)
                ->storingConversionsOnDisk(config('flux.media.conversion'))
                ->toMediaCollection(collectionName: $mediaItem->collection_name, diskName: $diskName);
        } else {
            $media = $mediaItem->model
                ->addMedia($file instanceof UploadedFile ? $file->path() : $file)
                ->setName($data['name'] ?? $filename)
                ->withCustomProperties($customProperties)
                ->storingConversionsOnDisk(config('flux.media.conversion'))
                ->toMediaCollection(collectionName: $mediaItem->collection_name, diskName: $diskName);
        }

        $media->forceFill([
            'id' => $id,
        ]);
        $media->save();

        return ResponseHelper::createArrayResponse(
            statusCode: 200,
            data: $media->withoutRelations(),
            additions: ['url' => $media->getUrl()]
        );
    }

    public function update(array $data): Model
    {
        $media = Media::query()
            ->whereKey($data['id'])
            ->first();

        $media->fill($data);
        $media->save();

        return $media->withoutRelations();
    }

    public function delete(string $id): array
    {
        $mediaItem = Media::query()
            ->whereKey($id)
            ->first();

        if (! $mediaItem) {
            return ResponseHelper::createArrayResponse(statusCode: 404, data: ['media' => 'media not found']);
        }

        $attributes = $mediaItem->getAttributes();
        $attributes['deleted_at'] = Carbon::now()->toDateTimeString();
        $attributes['deleted_by'] = Auth::id();
        $message = 'File: \'' . $mediaItem->file_name . '\' deleted by user: \'' . Auth::id() . '\'';
        Log::notice($message, array_merge(['uuid' => $mediaItem->uuid], $attributes));

        $mediaItem->delete();

        return ResponseHelper::createArrayResponse(statusCode: 204, statusMessage: 'media deleted');
    }

    private function customProperties(array $data, string $model): array
    {
        $settings = collect(Setting::query()
            ->where('key', 'media_custom_paths')
            ->first()
            ?->settings);

        $customProperties = $data['custom_properties'] ?? [];

        $modelSetting = $settings
            ->where('model', $model)
            ->first();

        if ($modelSetting) {
            foreach (($modelSetting['custom_properties'] ?? []) as $customProperty) {
                if (array_key_exists($customProperty, $data)) {
                    $customProperties += [$customProperty => (bool) $data[$customProperty]];
                } else {
                    $customProperties += [$customProperty => false];
                }
            }
        }

        return $customProperties;
    }

    private function validateFileExists(array $data): bool
    {
        $query = Media::query()
            ->where('model_type', $data['model_type'])
            ->where('model_id', $data['model_id'])
            ->where('collection_name', $data['collection_name'])
            ->where('name', $data['name']);

        if ($data['id'] ?? false) {
            $query->where('id', '!=', $data['id']);
        }

        return $query->exists();
    }
}
