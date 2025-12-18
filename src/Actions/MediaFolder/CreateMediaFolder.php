<?php

namespace FluxErp\Actions\MediaFolder;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\MediaFolder;
use FluxErp\Models\Pivots\MediaFolderModel;
use FluxErp\Rulesets\MediaFolder\CreateMediaFolderRuleset;

class CreateMediaFolder extends FluxAction
{
    public static function models(): array
    {
        return [MediaFolder::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateMediaFolderRuleset::class;
    }

    public function performAction(): MediaFolder
    {
        $mediaFolder = app(MediaFolder::class, ['attributes' => $this->getData()]);
        $mediaFolder->save();

        if (resolve_static(MediaFolderModel::class, 'query')
            ->whereIntegerInRaw('media_folder_id', $mediaFolder->ancestorKeys())
            ->where('model_type', $this->getData('model_type'))
            ->where('model_id', $this->getData('model_id'))
            ->doesntExist()
        ) {
            $model = morph_to($this->getData('model_type'), $this->getData('model_id'));
            $model->mediaFolders()->attach($mediaFolder->id);
        }

        return $mediaFolder->refresh();
    }
}
