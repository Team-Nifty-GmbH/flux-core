<?php

namespace FluxErp\Rulesets\Rule;

use FluxErp\Models\Rule;
use FluxErp\Rulesets\FluxRuleset;

class CreateRuleRuleset extends FluxRuleset
{
    protected static ?string $model = Rule::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:rules,uuid',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ];
    }
}
