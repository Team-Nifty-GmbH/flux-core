<?php

namespace FluxErp\Actions\Lot;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Lot;
use FluxErp\Rulesets\Lot\UpdateLotRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class UpdateLot extends FluxAction
{
    public static function models(): array
    {
        return [Lot::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateLotRuleset::class;
    }

    public function performAction(): Model
    {
        $lot = resolve_static(Lot::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $lot->fill($this->data);
        $lot->save();

        return $lot->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $lot = resolve_static(Lot::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        if (resolve_static(Lot::class, 'query')
            ->whereKeyNot($lot->getKey())
            ->where('product_id', $this->data['product_id'] ?? $lot->product_id)
            ->where('lot_number', $this->data['lot_number'] ?? $lot->lot_number)
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'lot_number' => ['The given lot number is already taken for this product'],
            ])->errorBag('updateLot');
        }
    }
}
