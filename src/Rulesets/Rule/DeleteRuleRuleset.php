<?php

namespace FluxErp\Rulesets\Rule;

use FluxErp\Models\Rule;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteRuleRuleset extends FluxRuleset
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
        ];
    }
}
