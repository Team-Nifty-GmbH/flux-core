<?php

namespace FluxErp\Actions\Lot;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Lot;
use FluxErp\Rulesets\Lot\DeleteLotRuleset;
use Illuminate\Validation\ValidationException;

class DeleteLot extends FluxAction
{
    protected Lot $lot;

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
        return $this->getLot()->delete();
    }

    protected function validateData(): void
    {
        parent::validateData();

        if ($this->getLot()->stockPostings()->exists()) {
            throw ValidationException::withMessages([
                'stock_postings' => ['The given lot has stock postings'],
            ])
                ->errorBag('deleteLot');
        }
    }

    protected function getLot(): Lot
    {
        return $this->lot ??= resolve_static(Lot::class, 'query')
            ->whereKey($this->getData('id'))
            ->firstOrFail();
    }
}
