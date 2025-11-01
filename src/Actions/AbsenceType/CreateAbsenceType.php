<?php

namespace FluxErp\Actions\AbsenceType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\AbsenceType;
use FluxErp\Rulesets\AbsenceType\CreateAbsenceTypeRuleset;
use Illuminate\Support\Arr;

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
        $data = $this->getData();
        $absencePolicies = Arr::pull($data, 'absence_policies');

        $absenceType = app(AbsenceType::class, ['attributes' => $data]);
        $absenceType->save();

        if ($absencePolicies) {
            $absenceType->absencePolicies()->attach($absencePolicies);
        }

        return $absenceType->refresh();
    }
}
