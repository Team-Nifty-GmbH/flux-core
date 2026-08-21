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

        $stock = resolve_static(WarehouseBin::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->stockPostings()
            ->sum('posting');

        if (bccomp((string) $stock, '0', 10) !== 0) {
            throw ValidationException::withMessages([
                'stock_postings' => ['The given warehouse bin still holds stock'],
            ])->errorBag('deleteWarehouseBin');
        }
    }
}
