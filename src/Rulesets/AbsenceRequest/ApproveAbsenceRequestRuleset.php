<?php

namespace FluxErp\Rulesets\AbsenceRequest;

use FluxErp\Models\AbsenceRequest;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Rules\ModelExists;

class ApproveAbsenceRequestRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(AbsenceRequest::class),
            ],
            'approval_note' => 'nullable|string|max:500',
        ];
    }
}