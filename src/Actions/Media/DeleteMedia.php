<?php

namespace FluxErp\Actions\Media;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Media;
use FluxErp\Rulesets\Media\DeleteMediaRuleset;

class DeleteMedia extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeleteMediaRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Media::class];
    }

    public function performAction(): ?bool
    {
        $mediaItem = app(Media::class)->query()
            ->whereKey($this->data['id'])
            ->first();

        return $mediaItem->delete();
    }
}
