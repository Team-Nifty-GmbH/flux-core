<?php

namespace FluxErp\Actions\WarehouseBin;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\WarehouseBin;
use FluxErp\Rulesets\WarehouseBin\DeleteWarehouseBinRuleset;
use Illuminate\Validation\ValidationException;

class DeleteWarehouseBin extends FluxAction
{
    private ?WarehouseBin $warehouseBin = null;

    public static function models(): array
    {
        return [WarehouseBin::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteWarehouseBinRuleset::class;
    }

    public function performAction(): ?bool
    {
        return $this->warehouseBin->delete();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $this->warehouseBin = resolve_static(WarehouseBin::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        if ($this->warehouseBin->stockPostings()->exists()) {
            throw ValidationException::withMessages([
                'stock_postings' => ['The given warehouse bin has stock postings'],
            ])->errorBag('deleteWarehouseBin');
        }

        if ($this->warehouseBin->getAllDescendantsQuery()->exists()) {
            throw ValidationException::withMessages([
                'children' => ['The given warehouse bin has child bins'],
            ])->errorBag('deleteWarehouseBin');
        }
    }
}
