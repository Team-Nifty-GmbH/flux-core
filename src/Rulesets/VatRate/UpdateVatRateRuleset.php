<?php

namespace FluxErp\Rulesets\VatRate;

use FluxErp\Models\VatRate;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateVatRateRuleset extends FluxRuleset
{
    protected static ?string $model = VatRate::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => VatRate::class]),
            ],
            'name' => 'sometimes|required|string|max:255',
            'rate_percentage' => 'sometimes|required|numeric|lt:1|min:0',
            'footer_text' => 'string|nullable',
            'is_default' => 'boolean',
            'is_purchase' => 'boolean',
            'is_sales' => 'boolean',
            'is_tax_exemption' => 'boolean',
        ];
    }
}
