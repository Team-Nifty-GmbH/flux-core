<?php

namespace FluxErp\Actions\AbsenceType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\AbsenceType;
use FluxErp\Rulesets\AbsenceType\CreateAbsenceTypeRuleset;

class CreateAbsenceType extends FluxAction
{
    public static function models(): array
    {
        return [AbsenceType::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateAbsenceTypeRuleset::class;
    }

    public function performAction(): AbsenceType
    {
        $absenceType = app(AbsenceType::class, ['attributes' => $this->getData()]);
        $absenceType->save();

        return $absenceType->fresh();
    }
}