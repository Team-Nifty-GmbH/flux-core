<?php

namespace FluxErp\Actions\AbsenceType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\AbsenceType;
use FluxErp\Rulesets\AbsenceType\DeleteAbsenceTypeRuleset;

class DeleteAbsenceType extends FluxAction
{
    public static function models(): array
    {
        return [AbsenceType::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteAbsenceTypeRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(AbsenceType::class, 'query')
            ->whereKey($this->getData('id'))
            ->first()
            ->delete();
    }
}