<?php

namespace FluxErp\Rulesets\AbsenceRequest;

use FluxErp\Enums\AbsenceRequestStateEnum;
use FluxErp\Models\AbsenceType;
use FluxErp\Models\Employee;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class CreateAbsenceRequestRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'absence_type_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => AbsenceType::class]),
            ],
            'employee_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Employee::class]),
            ],
            'state_enum' => [
                'nullable',
                'string',
                Rule::enum(AbsenceRequestStateEnum::class),
            ],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'sick_note_issued_date' => 'nullable|date',
            'reason' => 'nullable|string',
            'substitute_note' => 'nullable|string',
            'is_emergency' => 'boolean',

            'substitutes' => 'nullable|array',
            'substitutes.*' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Employee::class]),
            ],
        ];
    }
}
