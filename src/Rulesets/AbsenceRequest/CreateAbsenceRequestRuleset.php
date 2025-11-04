<?php

namespace FluxErp\Rulesets\AbsenceRequest;

use FluxErp\Enums\AbsenceRequestDayPartEnum;
use FluxErp\Enums\AbsenceRequestStateEnum;
use FluxErp\Models\AbsenceType;
use FluxErp\Models\Employee;
use FluxErp\Rules\EnumRule;
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
                app(ModelExists::class, ['model' => AbsenceType::class])
                    ->where('is_active', true),
            ],
            'employee_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Employee::class]),
            ],
            'state' => [
                'nullable',
                app(EnumRule::class, ['type' => AbsenceRequestStateEnum::class]),
            ],
            'day_part' => [
                'required',
                app(EnumRule::class, ['type' => AbsenceRequestDayPartEnum::class]),
            ],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start',
            'start_time' => [
                'required_if:day_part,time',
                'exclude_unless:day_part,time',
                Rule::anyOf([
                    'date_format:H:i',
                    'date_format:H:i:s',
                ]),
            ],
            'end_time' => [
                'required_if:day_part,time',
                'exclude_unless:day_part,time',
                'after:start_time',
                Rule::anyOf([
                    'date_format:H:i',
                    'date_format:H:i:s',
                ]),
            ],
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
