<?php

namespace FluxErp\Actions\PaymentRun;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PaymentRun;
use FluxErp\Rulesets\PaymentRun\DeletePaymentRunRuleset;

class DeletePaymentRun extends FluxAction
{
    public static function getRulesets(): string|array
    {
        return DeletePaymentRunRuleset::class;
    }

    public static function models(): array
    {
        return [PaymentRun::class];
    }

    public function performAction(): ?bool
    {
        return resolve_static(PaymentRun::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
