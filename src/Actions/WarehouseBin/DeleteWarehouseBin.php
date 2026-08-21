<?php

namespace FluxErp\Actions\WarehouseBin;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\WarehouseBin;
use FluxErp\Rulesets\WarehouseBin\DeleteWarehouseBinRuleset;
use Illuminate\Validation\ValidationException;

class DeleteWarehouseBin extends FluxAction
{
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
        return resolve_static(WarehouseBin::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $warehouseBin = resolve_static(WarehouseBin::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        if ($warehouseBin->stockPostings()->count() > 0) {
            throw ValidationException::withMessages([
                'stock_postings' => ['The given warehouse bin has stock postings'],
            ])->errorBag('deleteWarehouseBin');
        }

        if ($warehouseBin->getAllDescendantsQuery()->exists()) {
            throw ValidationException::withMessages([
                'children' => ['The given warehouse bin has child bins'],
            ])->errorBag('deleteWarehouseBin');
        }
    }
}
