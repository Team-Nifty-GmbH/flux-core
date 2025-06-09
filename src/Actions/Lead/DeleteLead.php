<?php

namespace FluxErp\Actions\Lead;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Lead;
use FluxErp\Rulesets\Lead\DeleteLeadRuleset;

class DeleteLead extends FluxAction
{
    public static function models(): array
    {
        return [Lead::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteLeadRuleset::class;
    }

    public function performAction(): bool
    {
        return resolve_static(Lead::class, 'query')
            ->whereKey($this->getData('id'))
            ->first()
            ->delete();
    }
}
