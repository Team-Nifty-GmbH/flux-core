<?php

namespace FluxErp\Rulesets\LeadLossReason;

use FluxErp\Models\LeadLossReason;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateLeadLossReasonRuleset extends FluxRuleset
{
    protected static ?string $model = LeadLossReason::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => LeadLossReason::class]),
            ],
            'name' => 'sometimes|required|string|max:255',
            'is_active' => 'boolean',
        ];
    }
}
