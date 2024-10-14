<?php

namespace FluxErp\Actions\Media;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Media;
use FluxErp\Rulesets\Media\UpdateMediaRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class UpdateMedia extends FluxAction
{
    protected bool $force = false;

    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(UpdateMediaRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Media::class];
    }

    public function force($force = true): static
    {
        $this->force = $force;

        return $this;
    }

    public function performAction(): Model
    {
        $media = resolve_static(Media::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $media->fill($this->data);
        $media->save();

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
        if ($mediaItem->getCollection()?->readOnly === true && ! $this->force) {
            throw ValidationException::withMessages([
                'collection_name' => [__('The media collection is read-only and cannot be modified.')],
            ]);
        }
    }
}
