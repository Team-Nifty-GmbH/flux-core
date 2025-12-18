<?php

namespace FluxErp\Actions\PriceList;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PriceList;
use FluxErp\Rulesets\PriceList\DeletePriceListRuleset;
use Illuminate\Validation\ValidationException;

class DeletePriceList extends FluxAction
{
    public static function models(): array
    {
        return [PriceList::class];
    }

    protected function getRulesets(): string|array
    {
        return DeletePriceListRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(PriceList::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    protected function validateData(): void
    {
        parent::validateData();

        if (resolve_static(PriceList::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->prices()
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'prices' => ['Price list has associated prices'],
            ])->errorBag('deletePriceList');
        }
    }
}
