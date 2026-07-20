<?php

namespace FluxErp\Rulesets\RebateAgreement;

use FluxErp\Models\Contact;
use FluxErp\Models\RebateAgreement;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class CreateRebateAgreementRuleset extends FluxRuleset
{
    protected static ?string $model = RebateAgreement::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:rebate_agreements,uuid',
            'contact_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Contact::class]),
            ],
            'name' => 'nullable|string|max:255',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
            'tiers' => 'required|array|min:1',
            'tiers.*.from_volume' => ['required', 'distinct', app(Numeric::class, ['min' => 0])],
            'tiers.*.percentage' => ['required', app(Numeric::class, ['min' => 0, 'max' => 1])],
            'is_active' => 'boolean',
        ];
    }
}
