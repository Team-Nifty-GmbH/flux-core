<?php

namespace FluxErp\Rulesets\AbsenceRequest;

use FluxErp\Models\AbsenceRequest;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class ChangeAbsenceRequestStateRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => AbsenceRequest::class]),
            ],
            'comment' => 'nullable|string|max:500',
        ];
    }
}
