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
                'boolean',
                'declined_if:is_win,true',
                'declined_if:is_loss,true',
            ],
            'is_win' => [
                'boolean',
                'declined_if:is_default,true',
                'declined_if:is_loss,true',
            ],
            'is_loss' => [
                'boolean',
                'declined_if:is_default,true',
                'declined_if:is_win,true',
            ],
        ];
    }
}
