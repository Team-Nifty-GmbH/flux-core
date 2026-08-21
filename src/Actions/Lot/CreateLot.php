<?php

namespace FluxErp\Actions\Lot;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Lot;
use FluxErp\Rulesets\Lot\CreateLotRuleset;
use Illuminate\Validation\ValidationException;

class CreateLot extends FluxAction
{
    public static function models(): array
    {
        return [Lot::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateLotRuleset::class;
    }

    public function performAction(): Lot
    {
        $lot = app(Lot::class, ['attributes' => $this->data]);
        $lot->save();

        return $lot->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        if (resolve_static(Lot::class, 'query')
            ->withTrashed()
            ->where('product_id', $this->data['product_id'])
            ->where('lot_number', $this->data['lot_number'])
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'lot_number' => ['The given lot number is already taken for this product'],
            ])->errorBag('createLot');
        }
    }
}
