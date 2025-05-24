<?php

namespace FluxErp\Rulesets\LeadState;

use FluxErp\Models\LeadState;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteLeadStateRuleset extends FluxRuleset
{
    protected static ?string $model = LeadState::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => LeadState::class]),
            ],
        ];
    }
}
