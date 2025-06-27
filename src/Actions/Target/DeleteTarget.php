<?php

namespace FluxErp\Actions\Target;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Target;
use FluxErp\Rulesets\Target\DeleteTargetRuleset;

class DeleteTarget extends FluxAction
{
    public static function models(): array
    {
        return [Target::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteTargetRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(Target::class, 'query')
            ->whereKey($this->getData('id'))
            ->first()
            ->delete();
    }
}
