<?php

namespace FluxErp\Rulesets\LeadLossReason;

use FluxErp\Models\LeadLossReason;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteLeadLossReasonRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = LeadLossReason::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => LeadLossReason::class]),
            ],
        ];
    }
}
