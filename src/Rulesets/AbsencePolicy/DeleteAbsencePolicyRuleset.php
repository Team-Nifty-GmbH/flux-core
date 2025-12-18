<?php

namespace FluxErp\Rulesets\AbsencePolicy;

use FluxErp\Models\AbsencePolicy;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteAbsencePolicyRuleset extends FluxRuleset
{
    protected static ?string $model = AbsencePolicy::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => AbsencePolicy::class]),
            ],
        ];
    }
}
