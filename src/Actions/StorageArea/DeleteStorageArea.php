<?php

namespace FluxErp\Actions\StorageArea;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\StorageArea;
use FluxErp\Rulesets\StorageArea\DeleteStorageAreaRuleset;
use Illuminate\Validation\ValidationException;

class DeleteStorageArea extends FluxAction
{
    protected ?StorageArea $storageArea = null;

    public static function models(): array
    {
        return [StorageArea::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteStorageAreaRuleset::class;
    }

    public function performAction(): ?bool
    {
        return $this->getStorageArea()?->delete();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $storageArea = $this->getStorageArea();
        $errors = [];

        if ($storageArea?->stockPostings()->exists()) {
            $errors['stock_postings'][] = 'The given storage area has stock postings';
        }

        if ($storageArea?->getAllDescendantsQuery()->exists()) {
            $errors['children'][] = 'The given storage area has child storage areas';
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)
                ->errorBag('deleteStorageArea');
        }
    }

    protected function getStorageArea(): ?StorageArea
    {
        return $this->storageArea ??= resolve_static(StorageArea::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();
    }
}
