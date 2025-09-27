<?php

namespace FluxErp\Actions\AbsencePolicy;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\AbsencePolicy;
use FluxErp\Rulesets\AbsencePolicy\DeleteAbsencePolicyRuleset;

class DeleteAbsencePolicy extends FluxAction
{
    public static function models(): array
    {
        return [AbsencePolicy::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteAbsencePolicyRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(AbsencePolicy::class, 'query')
            ->whereKey($this->getData('id'))
            ->firstOrFail()
            ->delete();
    }
}
