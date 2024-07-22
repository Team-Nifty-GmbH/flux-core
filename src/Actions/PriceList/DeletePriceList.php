<?php

namespace FluxErp\Actions\PriceList;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PriceList;
use FluxErp\Rulesets\PriceList\DeletePriceListRuleset;
use Illuminate\Validation\ValidationException;

class DeletePriceList extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeletePriceListRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [PriceList::class];
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
                'prices' => [__('Price list has associated prices')],
            ])->errorBag('deletePriceList');
        }
    }
}
