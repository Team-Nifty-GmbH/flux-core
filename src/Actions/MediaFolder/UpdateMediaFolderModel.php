<?php

namespace FluxErp\Actions\MediaFolder;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\MediaFolder;
use FluxErp\Rulesets\MediaFolder\UpdateMediaFolderModelRuleset;

class UpdateMediaFolderModel extends FluxAction
{
    public static function models(): array
    {
        return [MediaFolder::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateMediaFolderModelRuleset::class;
    }

    public function performAction(): array
    {
        $model = morph_to($this->getData('model_type'), $this->getData('model_id'));

        return $model->mediaFolders()->{$this->getData('method')}($this->getData('media_folders'));
    }

    protected function prepareForValidation(): void
    {
        $this->data['method'] ??= 'sync';
    }
}
