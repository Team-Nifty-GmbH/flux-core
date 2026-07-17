<?php

namespace FluxErp\Actions\RebateAgreement;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\RebateAgreement;
use FluxErp\Rulesets\RebateAgreement\CreateRebateAgreementRuleset;

class CreateRebateAgreement extends FluxAction
{
    public static function models(): array
    {
        return [RebateAgreement::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateRebateAgreementRuleset::class;
    }

    public function performAction(): RebateAgreement
    {
        $rebateAgreement = app(RebateAgreement::class, ['attributes' => $this->data]);
        $rebateAgreement->save();

        return $rebateAgreement->fresh();
    }
}
