<?php

namespace FluxErp\Actions\VatRate;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\VatRate;
use FluxErp\Rulesets\VatRate\CreateVatRateRuleset;

class CreateVatRate extends FluxAction
{
    public static function getRulesets(): string|array
    {
        return CreateVatRateRuleset::class;
    }

    public static function models(): array
    {
        return [VatRate::class];
    }

    public function performAction(): VatRate
    {
        $vatRate = app(VatRate::class, ['attributes' => $this->data]);
        $vatRate->save();

        return $vatRate->fresh();
    }
}
