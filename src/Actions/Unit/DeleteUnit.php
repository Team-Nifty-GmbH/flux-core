<?php

namespace FluxErp\Actions\Unit;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Unit;
use FluxErp\Rulesets\Unit\DeleteUnitRuleset;

class DeleteUnit extends FluxAction
{
    public static function getRulesets(): string|array
    {
        return DeleteUnitRuleset::class;
    }

    public static function models(): array
    {
        return [Unit::class];
    }

    public function performAction(): ?bool
    {
        return resolve_static(Unit::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
