<?php

namespace FluxErp\Actions\MediaFolder;

use FluxErp\Actions\FluxAction;
use FluxErp\Helpers\Helper;
use FluxErp\Models\MediaFolder;
use FluxErp\Rulesets\MediaFolder\UpdateMediaFolderRuleset;
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

    public function performAction(): MediaFolder
    {
        $mediaFolder = resolve_static(MediaFolder::class, 'query')
            ->whereKey($this->getData('id'))
            ->firstOrFail();

        $mediaFolder->fill($this->getData());
        $mediaFolder->save();

        return $mediaFolder->withoutRelations()->fresh();
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

        $mediaFolder = resolve_static(MediaFolder::class, 'query')
            ->whereKey($this->getData('id'))
            ->first(['id', 'parent_id', 'parent_collection']);

        $errors = [];
        if ($this->getData('parent_id')
            && $mediaFolder->parent_id !== $this->getData('parent_id')
        ) {
            if (Helper::checkCycle(MediaFolder::class, $mediaFolder, $this->getData('parent_id'))) {
                $errors[] = 'Cycle detected';
            }

            $parentFolder = resolve_static(MediaFolder::class, 'query')
                ->whereKey($this->getData('parent_id'))
                ->first(['id', 'max_files', 'is_readonly']);

            // Disallow moving folder into a readonly folder
            if ($parentFolder?->is_readonly) {
                $errors[] = 'Folder is read-only';
            }

            // Disallow moving folder into a single file folder
            if ($parentFolder?->max_files === 1) {
                $errors[] = 'Folder is a single file folder';
            }

            if ($errors) {
                throw ValidationException::withMessages([
                    'parent_id' => $errors,
                ])
                    ->errorBag('updateMediaFolder');
            }
        }

        if ($this->getData('parent_collection')
            && $mediaFolder->parent_collection !== $this->getData('parent_collection')
        ) {
            $mediaCollection = ($mediaFolder
                ->mediaFolderModel
                ?->model
                ?->getRegisteredMediaCollections() ?? collect()
            )
                ->firstWhere('name', $this->getData('parent_collection'));

            if (is_null($mediaCollection)) {
                $errors[] = 'Parent folder not found';
            } else {
                // Disallow moving folder into a readonly collection
                if ($mediaCollection->readOnly) {
                    $errors[] = 'Folder is read-only';
                }

                // Disallow moving folder into a single file collection
                if ($mediaCollection->singleFile) {
                    $errors[] = 'Folder is single file folder';
                }
            }

            if ($errors) {
                throw ValidationException::withMessages([
                    'parent_collection' => $errors,
                ])
                    ->errorBag('updateMediaFolder');
            }
        }
    }
}
