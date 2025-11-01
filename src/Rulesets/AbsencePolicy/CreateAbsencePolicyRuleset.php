<?php

namespace FluxErp\Rulesets\AbsencePolicy;

use FluxErp\Models\AbsencePolicy;
use FluxErp\Rulesets\FluxRuleset;

class CreateAbsencePolicyRuleset extends FluxRuleset
{
    protected static ?string $model = AbsencePolicy::class;

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'max_consecutive_days' => 'nullable|integer|min:1',
            'min_notice_days' => 'nullable|integer|min:0',
            'documentation_after_days' => [
                'required_if_accepted:requires_documentation',
                'exclude_unless:requires_documentation,true',
                'integer',
                'min:1',
            ],
            'can_select_substitute' => 'boolean',
            'is_active' => 'boolean',
            'requires_documentation' => 'boolean',
            'requires_reason' => 'boolean',
            'requires_substitute' => 'boolean',
        ];
    }
}
