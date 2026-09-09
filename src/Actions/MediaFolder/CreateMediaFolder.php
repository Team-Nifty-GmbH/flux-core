<?php

namespace FluxErp\Actions\MediaFolder;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\MediaFolder;
use FluxErp\Models\Pivots\MediaFolderModel;
use FluxErp\Rulesets\MediaFolder\CreateMediaFolderRuleset;
use Illuminate\Validation\ValidationException;

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

    protected function prepareForValidation(): void
    {
        if ($this->getData('parent_id')) {
            $this->data['parent_collection'] = null;
        }
    }

    protected function validateData(): void
    {
        parent::validateData();

        $errors = [];
        if ($this->getData('parent_id')) {
            $parentFolder = resolve_static(MediaFolder::class, 'query')
                ->whereKey($this->getData('parent_id'))
                ->first(['id', 'max_files', 'is_readonly']);

            // Disallow creating folder into a readonly folder
            if ($parentFolder?->is_readonly) {
                $errors[] = 'Folder is read-only';
            }

            // Disallow creating folder into a single file folder
            if ($parentFolder?->max_files === 1) {
                $errors[] = 'Folder is a single file folder';
            }

            if ($errors) {
                throw ValidationException::withMessages([
                    'parent_id' => $errors,
                ])
                    ->errorBag('createMediaFolder');
            }
        }

        if ($this->getData('parent_collection')) {
            $mediaCollection = (morph_to($this->getData('model_type'), $this->getData('model_id'))
                ?->getRegisteredMediaCollections() ?? collect()
            )
                ->firstWhere('name', $this->getData('parent_collection'));

            if (is_null($mediaCollection)) {
                $errors[] = 'Parent folder not found';
            } else {
                // Disallow creating folder into a readonly collection
                if ($mediaCollection->readOnly) {
                    $errors[] = 'Folder is read-only';
                }

                // Disallow creating folder into a single file collection
                if ($mediaCollection->singleFile) {
                    $errors[] = 'Folder is single file folder';
                }
            }

            if ($errors) {
                throw ValidationException::withMessages([
                    'parent_collection' => $errors,
                ])
                    ->errorBag('createMediaFolder');
            }
        }
    }
}
