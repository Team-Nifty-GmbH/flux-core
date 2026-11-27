<?php

namespace FluxErp\Rulesets\RebateAgreement;

use FluxErp\Models\RebateAgreement;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteRebateAgreementRuleset extends FluxRuleset
{
    protected static ?string $model = RebateAgreement::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => RebateAgreement::class]),
            ],
        ];
    }
}
