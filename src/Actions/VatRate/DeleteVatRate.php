<?php

namespace FluxErp\Actions\VatRate;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\VatRate;
use FluxErp\Rulesets\VatRate\DeleteVatRateRuleset;

class DeleteVatRate extends FluxAction
{
    public static function models(): array
    {
        return [VatRate::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteVatRateRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(VatRate::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
