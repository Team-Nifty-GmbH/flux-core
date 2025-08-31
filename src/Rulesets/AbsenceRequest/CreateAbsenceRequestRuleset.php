<?php

namespace FluxErp\Rulesets\AbsenceRequest;

use FluxErp\Enums\AbsenceRequestStatusEnum;
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
            'employee_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Employee::class]),
            ],
            'absence_type_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => AbsenceType::class]),
            ],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'sick_note_issued_date' => 'nullable|date',
            'substitute_employee_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Employee::class]),
            ],
            'substitute_note' => 'nullable|string|max:500',
            'reason' => 'nullable|string|max:500',
            'is_emergency' => 'boolean',
            'status' => [
                'nullable',
                'string',
                Rule::enum(AbsenceRequestStatusEnum::class),
            ],
        ];
    }
}
