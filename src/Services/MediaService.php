<?php

namespace FluxErp\Services;

use FluxErp\Actions\Media\DeleteMedia;
use FluxErp\Actions\Media\DeleteMediaCollection;
use FluxErp\Actions\Media\ReplaceMedia;
use FluxErp\Actions\Media\UpdateMedia;
use FluxErp\Actions\Media\UploadMedia;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class MediaService
{
    /**
     * @throws \League\Flysystem\FilesystemException
     */
    public function downloadPublic(string $filename, array $data): array
    {
        $mediaItem = resolve_static(Media::class, 'query')
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
        $mediaItem = resolve_static(Media::class, 'query')
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
        try {
            $media = UploadMedia::make($data)->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createArrayResponse(
                statusCode: 422,
                data: $e->errors()
            );
        }

        return ResponseHelper::createArrayResponse(
            statusCode: 201,
            data: $media,
            additions: ['url' => $media->getUrl()]
        );
    }

    public function replace(string $id, array $data): array
    {
        try {
            $media = ReplaceMedia::make(array_merge($data, ['id' => $id]))->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createArrayResponse(
                statusCode: 422,
                data: $e->errors()
            );
        }

        return ResponseHelper::createArrayResponse(
            statusCode: 200,
            data: $media,
            additions: ['url' => $media->getUrl()]
        );
    }

    public function update(array $data): Model
    {
        return UpdateMedia::make($data)->validate()->execute();
    }

    public function delete(string $id): array
    {
        try {
            DeleteMedia::make(['id' => $id])->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: $e->errors()
            );
        }

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'media deleted'
        );
    }

    public function deleteCollection(array $data): array
    {
        try {
            DeleteMediaCollection::make($data)->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createArrayResponse(
                statusCode: 422,
                data: $e->errors()
            );
        }

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'media collection deleted'
        );
    }
}
