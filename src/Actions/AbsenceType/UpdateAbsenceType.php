<?php

namespace FluxErp\Actions\AbsenceType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\AbsenceType;
use FluxErp\Rulesets\AbsenceType\UpdateAbsenceTypeRuleset;

class UpdateAbsenceType extends FluxAction
{
    public static function models(): array
    {
        return [AbsenceType::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateAbsenceTypeRuleset::class;
    }

    public function performAction(): AbsenceType
    {
        $absenceType = resolve_static(AbsenceType::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        $absenceType->fill($this->getData());
        $absenceType->save();

        return $absenceType->fresh();
    }
}