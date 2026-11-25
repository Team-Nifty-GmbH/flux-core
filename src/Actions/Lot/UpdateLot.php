<?php

namespace FluxErp\Actions\Lot;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Lot;
use FluxErp\Rulesets\Lot\UpdateLotRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class UpdateLot extends FluxAction
{
    protected Lot $lot;

    public static function models(): array
    {
        return [Lot::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateLotRuleset::class;
    }

    public function performAction(): ?Model
    {
        $lot = $this->getLot();
        $lot->fill($this->getData());
        $lot->save();

        return $lot->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        if (resolve_static(Lot::class, 'query')
            ->whereKeyNot($this->getData('id'))
            ->where('product_id', $this->getData('product_id', $this->getLot()->product_id))
            ->where('lot_number', $this->getData('lot_number', $this->getLot()->lot_number))
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'lot_number' => ['The given lot number is already taken for this product'],
            ])
                ->errorBag('updateLot');
        }
    }

    protected function getLot(): Lot
    {
        return $this->lot ??= resolve_static(Lot::class, 'query')
            ->whereKey($this->getData('id'))
            ->firstOrFail();
    }
}
