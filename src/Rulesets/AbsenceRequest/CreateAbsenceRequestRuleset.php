<?php

namespace FluxErp\Rulesets\AbsenceRequest;

use FluxErp\Models\User;
use FluxErp\Models\AbsenceType;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateAbsenceRequestRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                new ModelExists(User::class),
            ],
            'absence_type_id' => [
                'required',
                'integer',
                new ModelExists(AbsenceType::class),
            ],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_half_day' => 'nullable|in:full,morning,afternoon',
            'end_half_day' => 'nullable|in:full,morning,afternoon',
            'substitute_user_id' => [
                'nullable',
                'integer',
                new ModelExists(User::class),
            ],
            'reason' => 'nullable|string|max:500',
            'is_emergency' => 'boolean',
            'status' => 'nullable|in:draft,pending,approved,rejected,cancelled',
        ];
    }
}