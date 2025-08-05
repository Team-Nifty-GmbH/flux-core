<?php

namespace FluxErp\Actions\Media;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Media;
use FluxErp\Rulesets\Media\DeleteMediaCollectionRuleset;

class DeleteMediaCollection extends FluxAction
{
    public static function models(): array
    {
        return [Media::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteMediaCollectionRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(Media::class, 'query')
            ->where('model_type', $this->data['model_type'])
            ->where('model_id', $this->data['model_id'])
            ->where('collection_name', 'LIKE', $this->data['collection_name'] . '.%')
            ->orWhere('collection_name', $this->data['collection_name'])
            ->delete();
    }
}
