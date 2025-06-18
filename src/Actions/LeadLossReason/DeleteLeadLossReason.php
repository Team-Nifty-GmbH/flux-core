<?php

namespace FluxErp\Actions\LeadLossReason;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\LeadLossReason;
use FluxErp\Rulesets\LeadLossReason\DeleteLeadLossReasonRuleset;

class DeleteLeadLossReason extends FluxAction
{
    public static function models(): array
    {
        return [LeadLossReason::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteLeadLossReasonRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(LeadLossReason::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
