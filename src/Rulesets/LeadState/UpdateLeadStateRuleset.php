<?php

namespace FluxErp\Rulesets\LeadState;

use FluxErp\Models\LeadState;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class UpdateLeadStateRuleset extends FluxRuleset
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
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],
            'probability_percentage' => [
                'sometimes',
                app(Numeric::class, ['min' => 0, 'max' => 1]),
            ],
            'color' => 'nullable|hex_color',
            'is_default' => [
                'required_if_accepted:is_won',
                'required_if_accepted:is_lost',
                'boolean',
                'declined_if:is_won,true',
                'declined_if:is_lost,true',
            ],
            'is_won' => [
                'required_if_accepted:is_default',
                'required_if_accepted:is_lost',
                'boolean',
                'declined_if:is_default,true',
                'declined_if:is_lost,true',
            ],
            'is_lost' => [
                'required_if_accepted:is_default',
                'required_if_accepted:is_won',
                'boolean',
                'declined_if:is_default,true',
                'declined_if:is_won,true',
            ],
        ];
    }
}
