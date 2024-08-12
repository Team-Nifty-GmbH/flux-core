<?php

namespace FluxErp\Rulesets\VatRate;

use FluxErp\Models\VatRate;
use FluxErp\Rulesets\FluxRuleset;

class CreateVatRateRuleset extends FluxRuleset
{
    protected static ?string $model = VatRate::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:vat_rates,uuid',
            'name' => 'required|string',
            'rate_percentage' => 'required|numeric|lt:1|min:0',
            'footer_text' => 'string|nullable',
        ];
    }
}
