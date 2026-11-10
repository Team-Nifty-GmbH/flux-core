<?php

namespace FluxErp\Actions\Media;

use FluxErp\Actions\FluxAction;
use FluxErp\Contracts\HasMediaForeignKey;
use FluxErp\Models\Media;
use FluxErp\Rulesets\Media\ReplaceMediaRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileUnacceptableForCollection;

class ReplaceMedia extends FluxAction
{
    protected const string REPLACED_COLLECTION_NAME = '.replaced';

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
            ->lockForUpdate()
            ->first();

        if (
            is_null($mediaItem)
            || $mediaItem->collection_name === static::REPLACED_COLLECTION_NAME
        ) {
            throw ValidationException::withMessages([
                'id' => ['The media has already been replaced or deleted.'],
            ]);
        }

        $diskName = $this->getData('disk') ?? (
            $mediaItem->model->getRegisteredMediaCollections()
                ->where('name', $mediaItem->collection_name)
                ->first()
                ?->diskName ?: config('media-library.disk_name')
        );

        $this->moveOldMediaOutOfCollection($mediaItem);

        $file = $this->getData('media');
        $mediaItem->name = $this->getData('name');

        if ($this->getData('media_type') ?? false) {
            $fileAdder = $mediaItem->model->{'addMediaFrom' . $this->getData('media_type')}($file);
        } else {
            $fileAdder = $mediaItem->model->addMedia($file instanceof UploadedFile ? $file->path() : $file);
        }

        try {
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
        } catch (FileUnacceptableForCollection $e) {
            throw ValidationException::withMessages([
                'media' => [$e->getMessage()],
            ]);
        } finally {
            if (
                strtolower($this->getData('media_type') ?? '') === 'stream'
                && is_resource($this->getData('media'))
            ) {
                fclose($this->getData('media'));
            }
        }

        $this->replaceOldMediaOnModels($this->getData('id'), $media);
        DB::afterCommit(fn () => $this->deleteOldMedia($mediaItem->collection_name));

        return $media->withoutRelations();
    }

    protected function deleteOldMedia(string $collectionName): void
    {
        $delete = function () use ($collectionName): void {
            resolve_static(Media::class, 'query')
                ->whereKey($this->getData('id'))
                ->update(['collection_name' => $collectionName]);

            DeleteMedia::make(['id' => $this->getData('id')])
                ->execute();
        };

        // Same guard as FluxAction::execute(): no nested savepoint when this already
        // runs inside a transaction.
        if (DB::transactionLevel() === 0) {
            DB::transaction($delete);
        } else {
            $delete();
        }
    }

    protected function moveOldMediaOutOfCollection(Media $mediaItem): void
    {
        resolve_static(Media::class, 'query')
            ->whereKey($mediaItem->getKey())
            ->update(['collection_name' => static::REPLACED_COLLECTION_NAME]);
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
        collect(Relation::morphMap())
            ->filter(fn (string $class) => is_a($class, HasMediaForeignKey::class, true))
            ->each(
                fn (string $class) => resolve_static(
                    $class,
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
        if (data_get($mediaItem?->getCollection(), 'readOnly') === true && ! $this->force) {
            throw ValidationException::withMessages([
                'collection_name' => ['The media collection is read-only and cannot be modified.'],
            ]);
        }
    }
}
