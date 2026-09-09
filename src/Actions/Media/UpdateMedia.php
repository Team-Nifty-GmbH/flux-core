<?php

namespace FluxErp\Actions\Media;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Media;
use FluxErp\Rulesets\Media\UpdateMediaRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\MediaCollections\FileAdder;

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
            $this->data['file_name'] = Str::finish(
                app(FileAdder::class)->defaultSanitizer($this->data['file_name']),
                '.' . $media->extension
            );

            $paths[$media->disk][] = $media->getPathRelativeToRoot();

            foreach ($media->getGeneratedConversions() as $conversion => $generated) {
                $paths[$media->conversions_disk][] = $media->getPathRelativeToRoot($conversion);
            }
        }

        $media->fill($this->data);
        $media->save();

        $newFileName = Str::beforeLast($media->file_name, '.');

        foreach ($paths as $disk => $diskPaths) {
            foreach ($diskPaths as $path) {
                Storage::disk($disk)->move(
                    $path,
                    Str::beforeLast($path, '/') . '/'
                        . str_replace($currentFileName, $newFileName, basename($path))
                );
            }
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
