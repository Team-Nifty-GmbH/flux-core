<?php

namespace FluxErp\Actions\SerialNumberRange;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\SerialNumberRange;
use FluxErp\Rulesets\SerialNumberRange\DeleteSerialNumberRangeRuleset;

class DeleteSerialNumberRange extends FluxAction
{
    public static function getRulesets(): string|array
    {
        return DeleteSerialNumberRangeRuleset::class;
    }

    public static function models(): array
    {
        return [SerialNumberRange::class];
    }

    public function performAction(): ?bool
    {
        return resolve_static(SerialNumberRange::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
