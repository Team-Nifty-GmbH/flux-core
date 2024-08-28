<?php

namespace FluxErp\Actions\Media;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Media;
use FluxErp\Rulesets\Media\ReplaceMediaRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReplaceMedia extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(ReplaceMediaRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Media::class];
    }

    public function performAction(): Model
    {
        $mediaItem = resolve_static(Media::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $customProperties = CustomProperties::get($this->data, $mediaItem->model_type);
        $diskName = $this->data['disk'] ?? (
            $mediaItem->model->getRegisteredMediaCollections()
                ->where('name', $mediaItem->collection_name)
                ->first()
                ?->diskName ?: config('media-library.disk_name')
        );

        $file = $this->data['media'];
        $mediaItem->name = $this->data['name'];

        DeleteMedia::make(['id' => $this->data['id']])->execute();

        if ($this->data['media_type'] ?? false) {
            $fileAdder = $mediaItem->model->{'addMediaFrom' . $this->data['media_type']}($file);
        } else {
            $fileAdder = $mediaItem->model->addMedia($file instanceof UploadedFile ? $file->path() : $file);
        }

        $media = $fileAdder
            ->setName($this->data['name'])
            ->usingFileName($this->data['file_name'])
            ->withCustomProperties($customProperties)
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

        $media->forceFill([
            'id' => $this->data['id'],
        ]);
        $media->save();

        if (strtolower($this->data['media_type']) === 'stream') {
            fclose($this->data['media']);
        }

        return $media->withoutRelations();
    }

    protected function prepareForValidation(): void
    {
        $this->data['media_type'] = data_get($this->data, 'media_type');
        $this->data['model_type'] = resolve_static(Media::class, 'query')
            ->whereKey($this->data['id'] ?? null)
            ->first()
            ?->model_type;
    }

    protected function validateData(): void
    {
        parent::validateData();

        $mediaItem = resolve_static(Media::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $this->data['file_name'] = $this->data['file_name'] ?? (
            $this->data['media'] instanceof UploadedFile ?
                $this->data['media']->getClientOriginalName() :
                hash('sha512', microtime() . Str::uuid())
        );
        $this->data['name'] = $this->data['name'] ?? $this->data['file_name'];
        $this->data['collection_name'] ??= 'default';

        if (resolve_static(Media::class, 'query')
            ->where('model_type', $mediaItem->model_type)
            ->where('model_id', $mediaItem->model_id)
            ->where('collection_name', $mediaItem->collection_name)
            ->where('name', $this->data['name'])
            ->where('id', '!=', $this->data['id'])
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'filename' => [__('File name already exists')],
            ])->errorBag('replaceMedia');
        }
    }
}
