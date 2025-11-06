<?php

namespace FluxErp\Actions\Media;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Media;
use FluxErp\Rulesets\Media\DeleteMediaCollectionRuleset;
use Illuminate\Database\Eloquent\Builder;

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
            ->where('model_type', $this->getData('model_type'))
            ->where('model_id', $this->getData('model_id'))
            ->where(function (Builder $query): void {
                $query->where('collection_name', 'LIKE', $this->getData('collection_name') . '.%')
                    ->orWhere('collection_name', $this->getData('collection_name'));
            })
            ->delete();
    }
}
