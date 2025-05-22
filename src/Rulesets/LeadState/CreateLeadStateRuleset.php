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
                'nullable',
                'boolean',
                'declined_if:is_win,true',
                'declined_if:is_loss,true',
            ],
            'is_win' => [
                'nullable',
                'boolean',
                'declined_if:is_default,true',
                'declined_if:is_loss,true',
            ],
            'is_loss' => [
                'nullable',
                'boolean',
                'declined_if:is_default,true',
                'declined_if:is_win,true',
            ],
        ];
    }
}
