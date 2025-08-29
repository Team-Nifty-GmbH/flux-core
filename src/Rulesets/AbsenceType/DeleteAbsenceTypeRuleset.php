<?php

namespace FluxErp\Rulesets\AbsenceType;

use FluxErp\Models\AbsenceType;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteAbsenceTypeRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => AbsenceType::class]),
            ],
        ];
    }
}
