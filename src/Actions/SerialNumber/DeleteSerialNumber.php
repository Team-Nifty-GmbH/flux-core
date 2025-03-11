<?php

namespace FluxErp\Actions\SerialNumber;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\SerialNumber;
use FluxErp\Rulesets\SerialNumber\DeleteSerialNumberRuleset;

class DeleteSerialNumber extends FluxAction
{
    public static function models(): array
    {
        return [SerialNumber::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteSerialNumberRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(SerialNumber::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
