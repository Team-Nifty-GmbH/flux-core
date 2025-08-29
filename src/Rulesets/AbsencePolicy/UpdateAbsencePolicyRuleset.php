<?php

namespace FluxErp\Rulesets\AbsencePolicy;

use FluxErp\Models\AbsencePolicy;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateAbsencePolicyRuleset extends FluxRuleset
{
    protected static ?string $model = AbsencePolicy::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => AbsencePolicy::class]),
            ],
            'name' => 'sometimes|required|string|max:255',
            'max_consecutive_days' => 'nullable|integer|min:1',
            'min_notice_days' => 'nullable|integer|min:0',
            'requires_substitute' => 'boolean',
            'requires_documentation' => 'boolean',
            'documentation_after_days' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ];
    }
}
