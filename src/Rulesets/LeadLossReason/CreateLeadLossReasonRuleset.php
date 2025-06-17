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
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ];
    }
}
