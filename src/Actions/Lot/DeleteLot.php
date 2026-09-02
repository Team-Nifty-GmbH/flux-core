<?php

namespace FluxErp\Actions\Lot;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Lot;
use FluxErp\Rulesets\Lot\DeleteLotRuleset;
use Illuminate\Validation\ValidationException;

class DeleteLot extends FluxAction
{
    private ?Lot $lot = null;

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
        return $this->lot->delete();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $this->lot = resolve_static(Lot::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        if ($this->lot->stockPostings()->exists()) {
            throw ValidationException::withMessages([
                'stock_postings' => ['The given lot has stock postings'],
            ])->errorBag('deleteLot');
        }
    }
}
