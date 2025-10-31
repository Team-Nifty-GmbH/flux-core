<?php

namespace FluxErp\Actions\MediaFolder;

use FluxErp\Actions\FluxAction;
use FluxErp\Helpers\Helper;
use FluxErp\Models\MediaFolder;
use FluxErp\Rulesets\MediaFolder\UpdateMediaFolderRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class UpdateMediaFolder extends FluxAction
{
    public static function models(): array
    {
        return [MediaFolder::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateMediaFolderRuleset::class;
    }

    public function performAction(): Model
    {
        $mediaFolder = resolve_static(MediaFolder::class, 'query')
            ->whereKey($this->getData('id'))
            ->firstOrFail();

        $mediaFolder->fill($this->getData());
        $mediaFolder->save();

        return $mediaFolder->withoutRelations()->fresh();
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
                    ->errorBag('updateMediaFolder');
            }
        }

        if ($this->getData('parent_id')) {
            $mediaFolder = resolve_static(MediaFolder::class, 'query')
                ->whereKey($this->getData('id'))
                ->first();

            if (Helper::checkCycle(MediaFolder::class, $mediaFolder, $this->getData('parent_id'))) {
                throw ValidationException::withMessages([
                    'parent_id' => ['Cycle detected'],
                ])
                    ->errorBag('updateMediaFolder');
            }
        }
    }
}
