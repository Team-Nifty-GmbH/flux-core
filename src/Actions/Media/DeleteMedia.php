<?php

namespace FluxErp\Actions\Media;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Media;
use FluxErp\Rulesets\Media\DeleteMediaRuleset;
use Illuminate\Validation\ValidationException;

class DeleteMedia extends FluxAction
{
    protected bool $force = false;

    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeleteMediaRuleset::class, 'getRules');
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

    public function performAction(): ?bool
    {
        $mediaItem = resolve_static(Media::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        return $mediaItem->delete();
    }

    public function validateData(): void
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
