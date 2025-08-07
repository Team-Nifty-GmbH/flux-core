<?php

namespace FluxErp\Rulesets\AbsenceType;

use FluxErp\Models\Client;
use FluxErp\Models\AbsenceType;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Rules\ModelExists;

class UpdateAbsenceTypeRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(AbsenceType::class),
            ],
            'name' => 'sometimes|required|string|max:255',
            'color' => 'sometimes|required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_active' => 'boolean',
            'can_select_substitute' => 'boolean',
            'must_select_substitute' => 'boolean',
            'requires_proof' => 'boolean',
            'requires_reason' => 'boolean',
            'employee_can_create' => 'sometimes|required|in:yes,no,approval_required',
            'counts_as_work_day' => 'boolean',
            'counts_as_target_hours' => 'boolean',
            'requires_work_day' => 'boolean',
            'client_id' => [
                'sometimes',
                'required',
                'integer',
                new ModelExists(Client::class),
            ],
        ];
    }
}