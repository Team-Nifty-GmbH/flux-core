<?php

namespace FluxErp\Actions\StorageArea;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\StorageArea;
use FluxErp\Rulesets\StorageArea\CreateStorageAreaRuleset;
use Illuminate\Validation\ValidationException;

class CreateStorageArea extends FluxAction
{
    public static function models(): array
    {
        return [StorageArea::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateStorageAreaRuleset::class;
    }

    public function performAction(): StorageArea
    {
        $storageArea = app(StorageArea::class, ['attributes' => $this->getData()]);
        $storageArea->save();

        return $storageArea->refresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $errors = [];

        if (($parentId = $this->getData('parent_id'))
            && resolve_static(StorageArea::class, 'query')
                ->whereKey($parentId)
                ->where('warehouse_id', '!=', $this->getData('warehouse_id'))
                ->exists()
        ) {
            $errors['parent_id'][] = 'The parent storage area belongs to a different warehouse';
        }

        if (resolve_static(StorageArea::class, 'query')
            ->where('warehouse_id', $this->getData('warehouse_id'))
            ->where('code', $this->getData('code'))
            ->exists()
        ) {
            $errors['code'][] = 'The given code is already taken in this warehouse';
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)
                ->errorBag('createStorageArea');
        }
    }
}
