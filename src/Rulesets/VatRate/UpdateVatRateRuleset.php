<?php

namespace FluxErp\Rulesets\VatRate;

use FluxErp\Models\VatRate;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
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
            'name' => 'required|string',
            'rate_percentage' => [
                'required',
                app(Numeric::class, ['min' => 0, 'max' => 100]),
            ],
            'footer_text' => 'string|nullable',
        ];
    }
}
