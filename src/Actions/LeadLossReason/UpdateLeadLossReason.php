<?php

namespace FluxErp\Actions\LeadLossReason;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\LeadLossReason;
use FluxErp\Rulesets\LeadLossReason\UpdateLeadLossReasonRuleset;

class UpdateLeadLossReason extends FluxAction
{
    public static function models(): array
    {
        return [LeadLossReason::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateLeadLossReasonRuleset::class;
    }

    public function performAction(): LeadLossReason
    {
        $leadLossReason = resolve_static(LeadLossReason::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        $leadLossReason->fill($this->data);
        $leadLossReason->save();

        return $leadLossReason->withoutRelations()->fresh();
    }
}
