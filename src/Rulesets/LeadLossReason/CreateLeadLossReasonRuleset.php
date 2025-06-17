<?php

namespace FluxErp\Rulesets\LeadLossReason;

use FluxErp\Models\LeadLossReason;
use FluxErp\Rulesets\FluxRuleset;

class CreateLeadLossReasonRuleset extends FluxRuleset
{
    protected static ?string $model = LeadLossReason::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:contact_origins,uuid',
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ];
    }
}
