<?php

namespace FluxErp\Rulesets\AbsenceRequest;

use FluxErp\Models\AbsenceRequest;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteAbsenceRequestRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(AbsenceRequest::class),
            ],
        ];
    }
}