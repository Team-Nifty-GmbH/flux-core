<?php

namespace FluxErp\Actions\LeadState;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\LeadState;
use FluxErp\Rulesets\LeadState\DeleteLeadStateRuleset;

class DeleteLeadState extends FluxAction
{
    public static function models(): array
    {
        return [LeadState::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteLeadStateRuleset::class;
    }

    public function performAction(): bool
    {
        return resolve_static(LeadState::class, 'query')
            ->whereKey($this->getData('id'))
            ->first()
            ->delete();
    }
}
