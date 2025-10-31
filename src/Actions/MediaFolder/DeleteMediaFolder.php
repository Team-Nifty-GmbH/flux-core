<?php

namespace FluxErp\Actions\MediaFolder;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\MediaFolder;
use FluxErp\Rulesets\MediaFolder\DeleteMediaFolderRuleset;
use Illuminate\Validation\ValidationException;

class DeleteMediaFolder extends FluxAction
{
    public static function models(): array
    {
        return [MediaFolder::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteMediaFolderRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(MediaFolder::class, 'query')
            ->whereKey($this->getData('id'))
            ->first()
            ->delete();
    }

    protected function validateData(): void
    {
        parent::validateData();

        if ($this->getData('model_type') && $this->getData('model_id')) {
            $model = morph_to($this->getData('model_type'), $this->getData('model_id'));
            $ancestors = resolve_static(MediaFolder::class, 'query')
                ->whereKey($this->getData('id'))
                ->first()
                ?->ancestorKeys();

            if ($model->mediaFolders()
                ->whereKey(array_merge($ancestors, [$this->getData('id')]))
                ->doesntExist()
            ) {
                throw ValidationException::withMessages([
                    'id' => ['The media folder does not belong to the specified model.'],
                ])
                    ->errorBag('deleteMediaFolder');
            }
        }
    }
}
