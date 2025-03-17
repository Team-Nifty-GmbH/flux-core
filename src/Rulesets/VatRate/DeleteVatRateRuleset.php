<?php

namespace FluxErp\Rulesets\VatRate;

use FluxErp\Models\VatRate;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteVatRateRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = VatRate::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => VatRate::class]),
            ],
        ];
    }
}
