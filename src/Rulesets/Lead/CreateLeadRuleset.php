<?php

namespace FluxErp\Rulesets\Lead;

use FluxErp\Models\Address;
use FluxErp\Models\Lead;
use FluxErp\Models\LeadState;
use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class CreateLeadRuleset extends FluxRuleset
{
    protected static ?string $model = Lead::class;

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(CategoryRuleset::class, 'getRules'),
            resolve_static(TagRuleset::class, 'getRules')
        );
    }

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:leads,uuid',
            'address_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Address::class]),
            ],
            'lead_state_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => LeadState::class]),
            ],
            'recommended_by_address_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Address::class]),
            ],
            'user_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => User::class]),
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'loss_reason' => 'nullable|string',
            'start' => 'nullable|date',
            'end' => 'nullable|date|after_or_equal:start',
            'probability_percentage' => [
                'required',
                app(Numeric::class, ['min' => 0, 'max' => 1]),
            ],
            'expected_revenue' => [
                'nullable',
                app(Numeric::class, ['min' => 0]),
            ],
            'expected_gross_profit' => [
                'nullable',
                app(Numeric::class, ['min' => 0]),
            ],
            'score' => [
                'nullable',
                app(Numeric::class, ['min' => 0, 'max' => 5]),
            ],
        ];
    }
}
