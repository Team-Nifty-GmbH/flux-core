<?php

namespace FluxErp\Rulesets\AbsenceType;

use FluxErp\Models\AbsenceType;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Rules\ModelExists;

class DeleteAbsenceTypeRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(AbsenceType::class),
            ],
        ];
    }
}