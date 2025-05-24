<?php

namespace FluxErp\Rulesets\LeadState;

use FluxErp\Models\LeadState;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class CreateLeadStateRuleset extends FluxRuleset
{
    protected static ?string $model = LeadState::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:lead_states,uuid',
            'name' => 'required|string|max:255',
            'color' => 'nullable|hex_color',
            'probability_percentage' => [
                'nullable',
                app(Numeric::class, ['min' => 0, 'max' => 1]),
            ],
            'is_default' => [
                'boolean',
                'declined_if:is_won,true',
                'declined_if:is_lost,true',
            ],
            'is_won' => [
                'boolean',
                'declined_if:is_default,true',
                'declined_if:is_lost,true',
            ],
            'is_lost' => [
                'boolean',
                'declined_if:is_default,true',
                'declined_if:is_won,true',
            ],
        ];
    }
}
