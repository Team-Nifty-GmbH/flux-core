<?php

namespace FluxErp\Rulesets\VatRate;

use FluxErp\Models\VatRate;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class CreateVatRateRuleset extends FluxRuleset
{
    protected static ?string $model = VatRate::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:vat_rates,uuid',
            'name' => 'required|string|max:255',
            'rate_percentage' => [
                'required',
                app(Numeric::class, ['min' => 0]),
            ],
            'footer_text' => 'string|nullable',
            'is_default' => 'boolean',
            'is_purchase' => 'boolean',
            'is_sales' => 'boolean',
            'is_tax_exemption' => 'boolean',
        ];
    }
}
