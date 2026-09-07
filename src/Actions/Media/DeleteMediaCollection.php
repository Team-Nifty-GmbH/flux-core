<?php

namespace FluxErp\Actions\Media;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Media;
use FluxErp\Models\MediaFolder;
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
        $deleted = resolve_static(Media::class, 'query')
            ->where('model_type', $this->getData('model_type'))
            ->where('model_id', $this->getData('model_id'))
            ->where(function (Builder $query): void {
                $query->where('collection_name', 'LIKE', $this->getData('collection_name') . '.%')
                    ->orWhere('collection_name', $this->getData('collection_name'));
            })
            ->delete();

        resolve_static(MediaFolder::class, 'query')
            ->join('media_folder_model AS mfm', 'media_folders.id', '=', 'mfm.media_folder_id')
            ->where('mfm.model_type', $this->getData('model_type'))
            ->where('mfm.model_id', $this->getData('model_id'))
            ->where(function (Builder $query): void {
                $query->where('media_folders.parent_collection', 'LIKE', $this->getData('collection_name') . '.%')
                    ->orWhere('media_folders.parent_collection', $this->getData('collection_name'));
            })
            ->delete();

        return $deleted;
    }
}
