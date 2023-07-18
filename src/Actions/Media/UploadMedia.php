<?php

namespace FluxErp\Actions\Media;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\UploadMediaRequest;
use FluxErp\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UploadMedia extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new UploadMediaRequest())->rules();
    }

    public static function models(): array
    {
        return [Media::class];
    }

    public function execute(): Model
    {
        $modelInstance = $this->data['model_type']::query()
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
            ->storingConversionsOnDisk(config('flux.media.conversion'))
            ->toMediaCollection(collectionName: $this->data['collection_name'], diskName: $diskName);

        return $media->withoutRelations();
    }

    public function validate(): static
    {
        parent::validate();

        $this->data['file_name'] = $this->data['file_name'] ?? (
            $this->data['media'] instanceof UploadedFile ?
                $this->data['media']->getClientOriginalName() :
                hash('sha512', microtime() . Str::uuid())
        );
        $this->data['name'] = $this->data['name'] ?? $this->data['file_name'];
        $this->data['collection_name'] ??= 'default';

        if (Media::query()
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

        return $this;
    }
}
