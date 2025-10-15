<?php

namespace FluxErp\Actions\Media;

use FluxErp\Actions\FluxAction;
use FluxErp\Contracts\HasMediaForeignKey;
use FluxErp\Models\Media;
use FluxErp\Rulesets\Media\ReplaceMediaRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReplaceMedia extends FluxAction
{
    protected bool $force = false;

    public static function models(): array
    {
        return [Media::class];
    }

    protected static function getSuccessCode(): ?int
    {
        return parent::getSuccessCode() ?? Response::HTTP_OK;
    }

    protected function getRulesets(): string|array
    {
        return ReplaceMediaRuleset::class;
    }

    public function force($force = true): static
    {
        $this->force = $force;

        return $this;
    }

    public function performAction(): Model
    {
        $mediaItem = resolve_static(Media::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        $diskName = $this->getData('disk') ?? (
            $mediaItem->model->getRegisteredMediaCollections()
                ->where('name', $mediaItem->collection_name)
                ->first()
                ?->diskName ?: config('media-library.disk_name')
        );

        $file = $this->getData('media');
        $mediaItem->name = $this->getData('name');

        if ($this->getData('media_type') ?? false) {
            $fileAdder = $mediaItem->model->{'addMediaFrom' . $this->getData('media_type')}($file);
        } else {
            $fileAdder = $mediaItem->model->addMedia($file instanceof UploadedFile ? $file->path() : $file);
        }

        $media = $fileAdder
            ->setName($this->getData('name'))
            ->usingFileName($this->getData('file_name'))
            ->withCustomProperties($this->getData('custom_properties') ?? [])
            ->withProperties(
                Arr::except(
                    $this->data,
                    [
                        'model_type',
                        'model_id',
                        'media',
                        'media_type',
                        'categories',
                        'name',
                        'file_name',
                        'disk',
                        'conversion_disk',
                        'collection_name',
                        'mime_type',
                        'size',
                        'order_column',
                        'custom_properties',
                        'responsive_images',
                        'manipulations',
                    ]
                )
            )
            ->storingConversionsOnDisk(config('flux.media.conversion'))
            ->toMediaCollection(collectionName: $mediaItem->collection_name, diskName: $diskName);

        if (strtolower($this->getData('media_type')) === 'stream') {
            fclose($this->getData('media'));
        }

        $this->replaceOldMediaOnModels($this->getData('id'), $media);
        $this->deleteOldMedia();

        return $media->withoutRelations();
    }

    protected function deleteOldMedia(): void
    {
        DeleteMedia::make(['id' => $this->getData('id')])
            ->execute();
    }

    protected function prepareForValidation(): void
    {
        $this->data['media_type'] = $this->getData('media_type');
        $this->data['model_type'] = resolve_static(Media::class, 'query')
            ->whereKey($this->getData('id'))
            ->first()
            ?->model_type;
    }

    protected function replaceOldMediaOnModels(int|string $oldMediaId, Model $newMedia): void
    {
        model_info_all()
            ->filter(
                fn ($modelInfo) => is_a(
                    resolve_static($modelInfo->class, 'class'),
                    HasMediaForeignKey::class,
                    true
                )
            )
            ->each(
                fn ($modelInfo) => resolve_static(
                    $modelInfo->class,
                    'mediaReplaced',
                    [
                        'oldMediaId' => $oldMediaId,
                        'newMediaId' => $newMedia->getKey(),
                    ]
                )
            );
    }

    protected function validateData(): void
    {
        parent::validateData();

        $mediaItem = resolve_static(Media::class, 'query')
            ->whereKey($this->getData('id'))
            ->with('model')
            ->first(['id', 'model_type', 'model_id', 'collection_name']);

        $this->data['file_name'] = $this->getData('file_name') ?? (
            $this->getData('media') instanceof UploadedFile ?
                $this->getData('media')->getClientOriginalName() :
                hash('sha512', microtime() . Str::uuid())
        );
        $this->data['name'] = $this->getData('name') ?? $this->getData('file_name');
        $this->data['collection_name'] ??= 'default';

        // check if the media collection is read-only
        if (data_get($mediaItem->getCollection(), 'readOnly') === true && ! $this->force) {
            throw ValidationException::withMessages([
                'collection_name' => [__('The media collection is read-only and cannot be modified.')],
            ]);
        }
    }
}
