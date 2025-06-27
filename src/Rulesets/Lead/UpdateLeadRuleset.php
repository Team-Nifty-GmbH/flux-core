<?php

namespace FluxErp\Rulesets\Lead;

use FluxErp\Models\Address;
use FluxErp\Models\Lead;
use FluxErp\Models\LeadLossReason;
use FluxErp\Models\LeadState;
use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class UpdateLeadRuleset extends FluxRuleset
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
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Lead::class]),
            ],
            'address_id' => [
                'sometimes',
                'required',
                'integer',
                app(ModelExists::class, ['model' => Address::class]),
            ],
            'lead_loss_reason_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => LeadLossReason::class]),
            ],
            'lead_state_id' => [
                'sometimes',
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
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'loss_reason' => 'nullable|string',
            'start' => 'present|date|nullable',
            'end' => 'present|date|nullable|after_or_equal:start',
            'probability_percentage' => [
                'sometimes',
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
                'sometimes',
                app(Numeric::class, ['min' => 0, 'max' => 5]),
            ],
        ];
    }
}
