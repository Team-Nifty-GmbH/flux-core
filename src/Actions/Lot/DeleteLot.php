<?php

namespace FluxErp\Actions\Lot;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Lot;
use FluxErp\Rulesets\Lot\DeleteLotRuleset;
use Illuminate\Validation\ValidationException;

class DeleteLot extends FluxAction
{
    public static function models(): array
    {
        return [Lot::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteLotRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(Lot::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    protected function validateData(): void
    {
        parent::validateData();

        if (resolve_static(Lot::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->stockPostings()
            ->count() > 0
        ) {
            throw ValidationException::withMessages([
                'stock_postings' => ['The given lot has stock postings'],
            ])->errorBag('deleteLot');
        }
    }
}
