<?php

namespace FluxErp\Actions\Price;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Price;
use FluxErp\Rulesets\Price\DeletePriceRuleset;

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
            ->whereKey($this->getData('id'))
            ->first()
            ->delete();
    }
}
