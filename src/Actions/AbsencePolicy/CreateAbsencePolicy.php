<?php

namespace FluxErp\Actions\AbsencePolicy;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\AbsencePolicy;
use FluxErp\Rulesets\AbsencePolicy\CreateAbsencePolicyRuleset;

class CreateAbsencePolicy extends FluxAction
{
    public static function models(): array
    {
        return [AbsencePolicy::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateAbsencePolicyRuleset::class;
    }

    public function performAction(): AbsencePolicy
    {
        $absencePolicy = app(AbsencePolicy::class, ['attributes' => $this->getData()]);
        $absencePolicy->save();

        return $absencePolicy->refresh();
    }
}
