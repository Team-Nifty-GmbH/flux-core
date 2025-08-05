<?php

namespace FluxErp\Actions\LeadState;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\LeadState;
use FluxErp\Rulesets\LeadState\UpdateLeadStateRuleset;

class UpdateLeadState extends FluxAction
{
    public static function models(): array
    {
        return [LeadState::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateLeadStateRuleset::class;
    }

    public function performAction(): LeadState
    {
        $leadState = resolve_static(LeadState::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();
        $leadState->fill($this->getData());
        $leadState->save();

        return $leadState->withoutRelations()->fresh();
    }
}
