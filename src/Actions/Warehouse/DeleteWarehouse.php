<?php

namespace FluxErp\Actions\Warehouse;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Warehouse;
use FluxErp\Rulesets\Warehouse\DeleteWarehouseRuleset;
use Illuminate\Validation\ValidationException;

class DeleteWarehouse extends FluxAction
{
    public static function models(): array
    {
        return [Warehouse::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteWarehouseRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(Warehouse::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    protected function validateData(): void
    {
        parent::validateData();

        if (resolve_static(Warehouse::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->stockPostings()
            ->count() > 0
        ) {
            throw ValidationException::withMessages([
                'stock_postings' => [__('The given warehouse has stock postings')],
            ])->errorBag('deleteWarehouse');
        }
    }
}
