<?php

namespace FluxErp\Rulesets\Commission;

use FluxErp\Models\Commission;
use FluxErp\Models\VatRate;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateCommissionCreditNotesRuleset extends FluxRuleset
{
    protected static ?string $model = Commission::class;

    public function rules(): array
    {
        return [
            'vat_rate_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => VatRate::class]),
            ],
            'commissions' => 'required|array',
            'commissions.*.id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Commission::class]),
            ],
        ];
    }
}
