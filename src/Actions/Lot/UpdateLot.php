<?php

namespace FluxErp\Actions\Lot;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Lot;
use FluxErp\Rulesets\Lot\UpdateLotRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class UpdateLot extends FluxAction
{
    private ?Lot $lot = null;

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
        $this->lot->fill($this->getData());
        $this->lot->save();

        return $this->lot->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $this->lot = resolve_static(Lot::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        if (resolve_static(Lot::class, 'query')
            ->whereKeyNot($this->lot->getKey())
            ->where('product_id', $this->getData('product_id', $this->lot->product_id))
            ->where('lot_number', $this->getData('lot_number', $this->lot->lot_number))
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'lot_number' => ['The given lot number is already taken for this product'],
            ])->errorBag('updateLot');
        }
    }
}
