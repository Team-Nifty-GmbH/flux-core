<?php

namespace FluxErp\Actions\Price;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Price;
use FluxErp\Rulesets\Price\DeletePriceRuleset;
use Illuminate\Validation\ValidationException;

class DeletePrice extends FluxAction
{
    public static function models(): array
    {
        return [Price::class];
    }

    protected function getRulesets(): string|array
    {
        return DeletePriceRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(Price::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    protected function validateData(): void
    {
        parent::validateData();

        if (resolve_static(Price::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->orderPositions()
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'order_positions' => [__('Price has associated order positions')],
            ])->errorBag('deletePrice');
        }
    }
}
