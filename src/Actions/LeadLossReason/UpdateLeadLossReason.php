<?php

namespace FluxErp\Actions\LeadLossReason;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\LeadLossReason;
use FluxErp\Rulesets\LeadLossReason\UpdateLeadLossReasonRuleset;
use Illuminate\Database\Eloquent\Model;

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

    public function performAction(): Model
    {
        $leadLossReason = resolve_static(LeadLossReason::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $leadLossReason->fill($this->data);
        $leadLossReason->save();

        return $leadLossReason->withoutRelations()->fresh();
    }
}
