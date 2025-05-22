<?php

namespace FluxErp\Actions\LeadState;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\LeadState;
use FluxErp\Rulesets\LeadState\CreateLeadStateRuleset;

class CreateLeadState extends FluxAction
{
    public static function models(): array
    {
        return [LeadState::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateLeadStateRuleset::class;
    }

    public function performAction(): LeadState
    {
        $leadState = app(LeadState::class, ['attributes' => $this->getData()]);
        $leadState->save();

        return $leadState->fresh();
    }
}
