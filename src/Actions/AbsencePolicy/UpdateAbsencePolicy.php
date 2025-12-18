<?php

namespace FluxErp\Actions\AbsencePolicy;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\AbsencePolicy;
use FluxErp\Rulesets\AbsencePolicy\UpdateAbsencePolicyRuleset;

class UpdateAbsencePolicy extends FluxAction
{
    public static function models(): array
    {
        return [AbsencePolicy::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateAbsencePolicyRuleset::class;
    }

    public function performAction(): AbsencePolicy
    {
        $absencePolicy = resolve_static(AbsencePolicy::class, 'query')
            ->whereKey($this->getData('id'))
            ->firstOrFail();

        $absencePolicy->fill($this->data);
        $absencePolicy->save();

        return $absencePolicy->withoutRelations()->fresh();
    }
}
