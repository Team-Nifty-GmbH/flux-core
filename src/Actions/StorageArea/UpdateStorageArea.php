<?php

namespace FluxErp\Actions\StorageArea;

use FluxErp\Actions\FluxAction;
use FluxErp\Helpers\Helper;
use FluxErp\Models\StorageArea;
use FluxErp\Rulesets\StorageArea\UpdateStorageAreaRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class UpdateStorageArea extends FluxAction
{
    protected ?StorageArea $storageArea = null;

    public static function models(): array
    {
        return [StorageArea::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateStorageAreaRuleset::class;
    }

    public function performAction(): ?Model
    {
        $storageArea = $this->getStorageArea();
        $storageArea?->fill($this->getData());
        $storageArea?->save();

        return $storageArea?->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $storageArea = $this->getStorageArea();
        $warehouseId = $this->getData('warehouse_id', $storageArea?->warehouse_id);
        $errors = [];

        if (($parentId = $this->getData('parent_id', $storageArea?->parent_id))
            && resolve_static(StorageArea::class, 'query')
                ->whereKey($parentId)
                ->where('warehouse_id', '!=', $warehouseId)
                ->exists()
        ) {
            $errors['parent_id'][] = 'The parent storage area belongs to a different warehouse';
        }

        if ($this->getData('parent_id')
            && Helper::checkCycle(StorageArea::class, $storageArea, $this->getData('parent_id'))
        ) {
            $errors['parent_id'][] = 'Cycle detected';
        }

        if ($warehouseId != $storageArea?->warehouse_id
            && $storageArea?->getAllDescendantsQuery()->exists()
        ) {
            $errors['warehouse_id'][] = 'The given storage area has child storage areas in its current warehouse';
        }

        if (resolve_static(StorageArea::class, 'query')
            ->whereKeyNot($this->getData('id'))
            ->where('warehouse_id', $warehouseId)
            ->where('code', $this->getData('code', $storageArea?->code))
            ->exists()
        ) {
            $errors['code'][] = 'The given code is already taken in this warehouse';
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)
                ->errorBag('updateStorageArea');
        }
    }

    protected function getStorageArea(): ?StorageArea
    {
        return $this->storageArea ??= resolve_static(StorageArea::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();
    }
}
