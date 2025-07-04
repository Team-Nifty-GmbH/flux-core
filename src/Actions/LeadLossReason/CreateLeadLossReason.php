<?php

namespace FluxErp\Actions\LeadLossReason;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\LeadLossReason;
use FluxErp\Rulesets\LeadLossReason\CreateLeadLossReasonRuleset;

class CreateLeadLossReason extends FluxAction
{
    public static function models(): array
    {
        return [LeadLossReason::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateLeadLossReasonRuleset::class;
    }

    public function performAction(): LeadLossReason
    {
        $leadLossReason = app(LeadLossReason::class, ['attributes' => $this->data]);
        $leadLossReason->save();

        return $leadLossReason->refresh();
    }
}
