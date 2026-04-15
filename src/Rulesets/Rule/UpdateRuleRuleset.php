<?php

namespace FluxErp\Rulesets\Rule;

use FluxErp\Models\Rule;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateRuleRuleset extends FluxRuleset
{
    protected static ?string $model = Rule::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Rule::class]),
            ],
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ];
    }
}
