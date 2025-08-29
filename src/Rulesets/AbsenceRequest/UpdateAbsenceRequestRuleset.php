<?php

namespace FluxErp\Rulesets\AbsenceRequest;

use FluxErp\Models\AbsenceRequest;
use FluxErp\Models\Employee;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateAbsenceRequestRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => AbsenceRequest::class]),
            ],
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'sick_note_issued_date' => 'nullable|date',
            'substitute_employee_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Employee::class]),
            ],
            'substitute_note' => 'nullable|string|max:500',
            'reason' => 'nullable|string|max:500',
            'is_emergency' => 'boolean',
        ];
    }
}
