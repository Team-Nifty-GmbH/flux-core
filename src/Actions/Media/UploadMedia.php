<?php

namespace FluxErp\Actions\Media;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Media;
use FluxErp\Rulesets\Media\UploadMediaRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UploadMedia extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(UploadMediaRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Media::class];
    }

    public function performAction(): Model
    {
        $modelInstance = morphed_model($this->data['model_type'])::query()
            ->whereKey($this->data['model_id'])
            ->first();

        $customProperties = CustomProperties::get($this->data, $this->data['model_type']);

        $diskName = $this->data['disk'] ?? (
            $modelInstance->getRegisteredMediaCollections()
                ->where('name', $this->data['collection_name'])
                ->first()
                ?->diskName ?: config('media-library.disk_name')
        );
        $file = $this->data['media'];

        if ($this->data['media_type'] ?? false) {
            $fileAdder = $modelInstance->{'addMediaFrom' . $this->data['media_type']}($file);
        } else {
            $fileAdder = $modelInstance->addMedia($file instanceof UploadedFile ? $file->path() : $file);
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
            ->toMediaCollection(collectionName: $this->data['collection_name'], diskName: $diskName);

        if (strtolower($this->data['media_type'] ?? false) === 'stream') {
            fclose($this->data['media']);
        }

        return $media->withoutRelations();
    }

    protected function prepareForValidation(): void
    {
        $this->data['file_name'] ??= match (true) {
            data_get($this->data, 'media') instanceof UploadedFile => $this->data['media']->getClientOriginalName(),
            file_exists(data_get($this->data, 'media', '')) => basename($this->data['media']),
            default => hash('sha512', microtime() . Str::uuid()),
        };
        $this->data['name'] ??= $this->data['file_name'];
        $this->data['collection_name'] ??= 'default';
    }

    protected function validateData(): void
    {
        parent::validateData();

        if (resolve_static(Media::class, 'query')
            ->where('model_type', $this->data['model_type'])
            ->where('model_id', $this->data['model_id'])
            ->where('collection_name', $this->data['collection_name'])
            ->where('name', $this->data['name'])
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'filename' => [__('File name already exists')],
            ])->errorBag('uploadMedia');
        }
    }
}
