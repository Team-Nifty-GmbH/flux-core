<?php

namespace FluxErp\Actions\Media;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Media;
use FluxErp\Rulesets\Media\UpdateMediaRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UpdateMedia extends FluxAction
{
    protected bool $force = false;

    public static function models(): array
    {
        return [Media::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateMediaRuleset::class;
    }

    public function force($force = true): static
    {
        $this->force = $force;

        return $this;
    }

    public function performAction(): Model
    {
        /** @var Media $media */
        $media = resolve_static(Media::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $currentFileName = Str::beforeLast($media->file_name, '.');
        $paths = [];
        if (data_get($this->data, 'file_name')) {
            $this->data['file_name'] = Str::finish($this->data['file_name'], '.' . $media->extension);

            $paths[] = $media->getPath();

            foreach ($media->getGeneratedConversions() as $conversion => $generated) {
                $paths[] = $media->getPath($conversion);
            }
        }

        $media->fill($this->data);
        $media->save();

        foreach ($paths as $path) {
            Storage::disk($media->disk)
                ->move($path, str_replace($currentFileName, Str::beforeLast($media->file_name, '.'), $path));
        }

        return $media->withoutRelations();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $mediaItem = resolve_static(Media::class, 'query')
            ->whereKey($this->data['id'])
            ->with('model')
            ->first(['id', 'model_type', 'model_id', 'collection_name']);

        // check if the media collection is read-only
        if (data_get($mediaItem->getCollection(), 'readOnly') === true && ! $this->force) {
            throw ValidationException::withMessages([
                'collection_name' => ['The media collection is read-only and cannot be modified.'],
            ]);
        }
    }
}
