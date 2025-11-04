<?php

namespace FluxErp\Rulesets\AbsenceRequest;

use FluxErp\Enums\AbsenceRequestDayPartEnum;
use FluxErp\Enums\AbsenceRequestStateEnum;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Models\Employee;
use FluxErp\Rules\EnumRule;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class UpdateAbsenceRequestRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => AbsenceRequest::class])
                    ->where('state', '!=', AbsenceRequestStateEnum::Approved->value),
            ],
            'day_part' => [
                'required_with:start_time,end_time',
                app(EnumRule::class, ['type' => AbsenceRequestDayPartEnum::class]),
            ],
            'start_date' => 'required_with:end_date|date',
            'end_date' => 'required_with:start_date|date|after_or_equal:start_date',
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
            'substitute_note' => 'nullable|string|max:500',
            'reason' => 'nullable|string|max:500',
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
