<?php

namespace FluxErp\Actions\VatRate;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\VatRate;
use FluxErp\Rulesets\VatRate\CreateVatRateRuleset;

class CreateVatRate extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateVatRateRuleset::class, 'getRules');
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
